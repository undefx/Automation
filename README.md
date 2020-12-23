# Automation

A simple way to define, manage, and automate tasks.

## local development

Clone this repo into a subdir at `repos/undefx`. For example:

```sh
tree -L 3 .
```

```
.
└── repos
    └── undefx
        └── Automation

```

Install docker.

Create a docker network:

```sh
docker network create --driver bridge automation-net
```

Build the images:

```sh
# database
docker build -t automation_database -f repos/undefx/Automation/dev/docker/database/Dockerfile .

# website
docker build -t automation_web -f repos/undefx/Automation/dev/docker/web/Dockerfile .

# driver
docker build -t automation_driver -f repos/undefx/Automation/dev/docker/driver/Dockerfile .
```

Run the containers:

```sh
# database
docker run --rm -p 127.0.0.1:13306:3306 --network automation-net --name automation_database automation_database

# website
docker run --rm -p 127.0.0.1:10080:80 --network automation-net --name automation_web automation_web

# driver
docker run --rm --network automation-net --name automation_driver automation_driver
```

You should now have a fully functioning local instance of Automation running.
You can view the web console at http://localhost:10080/
