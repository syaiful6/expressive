EMBERAPP = shopiejs
STATICPATH = public/assets

.PHONY: run compile-static cs-fix test check debug-mail

run:
	php -S 0.0.0.0:8080 -t public/ public/index.php

compile-static:
	rm -r $(STATICPATH)
	broccoli build $(STATICPATH)

cs-fix:
	vendor/bin/phpcbf

cs-info:
	vendor/bin/phpcs

test:
	vendor/bin/phpunit

check: cs-info test

debug-mail:
	python -m smtpd -n -c DebuggingServer localhost:1025
