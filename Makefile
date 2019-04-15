
build:
	docker build -t socket-server .
	docker run --rm --name composer -v $(PWD):/app composer install

run:
	docker run -d --rm --name socket-server -p 2080:2080 -p 2081:2081 -v $(PWD):/usr/src/myapp socket-server
kill:
	docker kill socket-server
