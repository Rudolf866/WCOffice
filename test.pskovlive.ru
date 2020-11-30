server {
        server_name test.pskovlive.ru;
        root /spool/www/pskovlive.ru/test.pskovlive.ru/public_html;
        access_log    /spool/www/pskovlive.ru/test.pskovlive.ru/logs/nginx_access.log;
        error_log     /spool/www/pskovlive.ru/test.pskovlive.ru/logs/nginx_error.log;

        index index.php;

        charset windows-1251;

        location = /favicon.ico {
                log_not_found off;
                access_log off;
        }

        location = /robots.txt {
                allow all;
                log_not_found off;
                access_log off;
        }

        location / {
                try_files $uri $uri/ /index.php?$args;
        }

        location ~ \.php$ {
                include fastcgi.conf;
                fastcgi_intercept_errors on;
                fastcgi_pass php;
        }

        location ~* \.(jpg|jpeg|gif|png|ico|css|zip|tgz|gz|rar|bz2|doc|xls|exe|pdf|ppt|txt|tar|mid|midi|wav|bmp|rtf|js)$ {
                expires max;
                log_not_found off;
        }
}
