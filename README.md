# Supermetrics API post stats calculation demo

Calculates statistics from Supermetrics posts API incrementally, page by page, 
without loading the full dataset into memory.

Install:
```
cp .env.dist .env && composer install
```

Run functional tests:
```
ENV=test ./vendor/bin/behat
```

Run unit tests:
```
./vendor/bin.phpspec run
```

Generate post statistics report:
```
./bin/console post:stats
```