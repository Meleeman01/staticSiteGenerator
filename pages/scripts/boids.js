 const canvas = document.getElementById('boidsCanvas');
    const ctx = canvas.getContext('2d');

    // Resize canvas to match window size
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    // Boid class
    class Boid {
        constructor(x, y) {
            this.position = { x, y };
            this.velocity = { x: Math.random() - 0.5, y: Math.random() - 0.5 };
            this.acceleration = { x: 0, y: 0 };
        }

        // Boid behaviors
        cohesion(boids) {
            let center = { x: 0, y: 0 };
            let count = 0;
            for (let other of boids) {
                if (other !== this) {
                    center.x += other.position.x;
                    center.y += other.position.y;
                    count++;
                }
            }
            if (count > 0) {
                center.x /= count;
                center.y /= count;
                return { x: (center.x - this.position.x) / 100, y: (center.y - this.position.y) / 100 };
            }
            return { x: 0, y: 0 };
        }

        separation(boids) {
            let steer = { x: 0, y: 0 };
            let count = 0;
            for (let other of boids) {
                if (other !== this) {
                    let d = distance(this.position, other.position);
                    if (d < 50) {
                        let diff = { 
                            x: this.position.x - other.position.x, 
                            y: this.position.y - other.position.y
                        };
                        steer.x += diff.x / d;
                        steer.y += diff.y / d;
                        count++;
                    }
                }
            }
            if (count > 0) {
                steer.x /= count;
                steer.y /= count;
            }
            return steer;
        }

        alignment(boids) {
            let avgVelocity = { x: 0, y: 0 };
            let count = 0;
            for (let other of boids) {
                if (other !== this) {
                    avgVelocity.x += other.velocity.x;
                    avgVelocity.y += other.velocity.y;
                    count++;
                }
            }
            if (count > 0) {
                avgVelocity.x /= count;
                avgVelocity.y /= count;
                avgVelocity.x = (avgVelocity.x - this.velocity.x) / 8;
                avgVelocity.y = (avgVelocity.y - this.velocity.y) / 8;
                return avgVelocity;
            }
            return { x: 0, y: 0 };
        }

        // Apply forces and update position
        update(boids) {
            let cohesionForce = this.cohesion(boids);
            let separationForce = this.separation(boids);
            let alignmentForce = this.alignment(boids);

            this.acceleration.x = cohesionForce.x + separationForce.x + alignmentForce.x;
            this.acceleration.y = cohesionForce.y + separationForce.y + alignmentForce.y;

            this.velocity.x += this.acceleration.x;
            this.velocity.y += this.acceleration.y;
            this.position.x += this.velocity.x;
            this.position.y += this.velocity.y;

            // Wrap around the screen
            this.position.x = (this.position.x + canvas.width) % canvas.width;
            this.position.y = (this.position.y + canvas.height) % canvas.height;
        }

        // Draw the boid
        draw() {
            ctx.beginPath();
            ctx.arc(this.position.x, this.position.y, 2, 0, Math.PI * 2);
            ctx.fillStyle = '#000';
            ctx.fill();
        }
    }

    // Helper function to calculate distance
    function distance(p1, p2) {
        let dx = p1.x - p2.x;
        let dy = p1.y - p2.y;
        return Math.sqrt(dx * dx + dy * dy);
    }

    // Initialize boids
    const boids = [];
    for (let i = 0; i < 100; i++) {
        boids.push(new Boid(Math.random() * canvas.width, Math.random() * canvas.height));
    }

    // Animation loop
    function animate() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        for (let boid of boids) {
            boid.update(boids);
            boid.draw();
        }

        requestAnimationFrame(animate);
    }

    animate();