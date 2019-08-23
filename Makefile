default: public/style.css

public/style.css: styles/*.scss
	sassc styles/style.scss public/style.css

# vim: set noet:
