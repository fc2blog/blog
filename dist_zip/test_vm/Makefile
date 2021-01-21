PHONY:
image:
	# alignment local UID/GID and docker UID/GID for Linux dev env.
	$(eval UID := $(shell id -u))
	$(eval GID := $(shell id -g))
	docker build -t fc2_dist_test_vm --build-arg PUID=$(UID) --build-arg PGID=$(GID) .

PHONY:
image-no-cache:
	# alignment local UID/GID and docker UID/GID for Linux dev env.
	$(eval UID := $(shell id -u))
	$(eval GID := $(shell id -g))
	docker build -t fc2_dist_test_vm --no-cache --build-arg PUID=$(UID) --build-arg PGID=$(GID) .

PHONY:
run:
	docker run --rm -it fc2_dist_test_vm sh -c "/startup.sh ; bash"

PHONY:
bash:
	docker run --rm -it fc2_dist_test_vm bash

