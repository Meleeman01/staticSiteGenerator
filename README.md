#PHP Static Site Generator!

create some pages in the pages directory,
using md is required, expect the partials, css, and images folders in the pages directory

below is an example of the structure you would be working with
```
pages
	|_css
		|_main.css
	|_images
			|_example.png literally any images here.
	|_partials
			|_header.md
			|_footer.md
	index.md
	about.md
	contact.md


```

links.csv is there to define the order of your links. if one exists, the generator will try
to put the links in the order you specify seperated by a comma. remember that index means 'home',
if there is a mistake in your csv list or the list is the wrong size the generator will set them in order as they appeared in the directory. 

run php generate.php to generate the site to the output.

---

##Deployment

to deploy on localhost simply run "php -S localhost:8000" in the output directory;

