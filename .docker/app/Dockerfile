FROM centos/systemd
LABEL maintainer="Jot"

RUN yum install -y git
RUN yum install -y httpd
RUN yum -y install http://rpms.remirepo.net/enterprise/remi-release-7.rpm
RUN yum -y install epel-release yum-utils
RUN yum install -y which
RUN yum-config-manager --disable remi-php54
RUN yum-config-manager --enable remi-php73
RUN yum -y install php php-cli php-fpm php-mysqlnd php-zip php-devel php-gd php-mcrypt php-mbstring php-curl php-xml php-pear php-bcmath php-json
RUN rm /etc/php-fpm.d/www.conf
RUN mkdir /var/run/php-fpm
COPY .docker/app/php-fpm/www.conf /etc/php-fpm.d/www.conf
COPY .docker/app/apache2/divoc-jeopardy.conf /etc/httpd/conf.d/divoc-jeopardy.conf

RUN php-fpm -D
#RUN chmod 0666 /var/run/php-fpm/php7.3-fpm.sock
RUN httpd -k start

COPY . /var/www/jeopardy/
WORKDIR /var/www/jeopardy
RUN cd /var/www/jeopardy