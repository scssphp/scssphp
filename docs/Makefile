.PHONY: serve highlight-css
serve: vendor/bundle
	# site should be available at http://localhost:4000/scssphp/
	bundle exec jekyll serve

highlight-css: vendor/bundle
	bundle exec rougify style github > _sass/_rouge.scss

vendor/bundle: Gemfile
	bundle install
