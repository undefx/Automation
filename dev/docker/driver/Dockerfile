# start with a standard CentOS image
FROM centos:7

# set timezome
RUN ln -s -f /usr/share/zoneinfo/America/New_York /etc/localtime

# install perl
RUN yum install -y perl perl-DBD-MySQL

# specify a working directory inside the container
WORKDIR /usr/src/app

# copy sources
COPY repos/undefx/Automation/driver/ driver

# use development credentials
RUN sed -i "s/my \$dbHost = '';/my \$dbHost = 'automation_database';/" driver/automation.pl
RUN sed -i "s/my \$dbName = '';/my \$dbName = 'automation';/" driver/automation.pl
RUN sed -i "s/my \$dbUser = '';/my \$dbUser = 'user';/" driver/automation.pl
RUN sed -i "s/my \$dbPass = '';/my \$dbPass = 'pass';/" driver/automation.pl

# run automation
CMD perl driver/automation.pl
