FROM harbor.devopshub.cn/containerdesktop/php-debug:latest

COPY xdebug.ini /etc/php/7.0/mods-available/xdebug.ini

EXPOSE 80
CMD ["start-container"]