
build:
	docker build -t json-rpc-socket-server .
	docker run --rm --name composer -v $(PWD):/app composer install

run:
	docker run                      \
	-d                              \
	--name json-rpc-socket-server   \
	-p 2024:2024                    \
	-p 2080:2080                    \
	-p 2081:2081                    \
	-v $(PWD):/usr/src/myapp        \
	json-rpc-socket-server

kill:
	docker kill json-rpc-socket-server

restart: run kill

destroy:
	docker rm json-rpc-socket-server
	docker rmi json-rpc-socket-server

logs:
	docker logs json-rpc-socket-server