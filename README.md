# Recruitment Task: Volt

[![CI](https://github.com/gander/Recruitment-Task__Volt/actions/workflows/ci.yml/badge.svg)](https://github.com/gander/Recruitment-Task__Volt/actions/workflows/ci.yml)

Task: a Symfony API that compares two GitHub repositories. The `GET /api?repo1=owner/name&repo2=owner/name` endpoint fetches the number of stars, forks, watchers and pull requests of both repositories from the GitHub API (the names are validated) and returns the comparison result as JSON.

## Requirements

- Docker Engine with Docker Compose v2 (the only dependency; neither PHP nor Composer is needed on the host).
- `curl` for the usage examples.

## Install

```bash
docker compose up --build -d --wait
```

## Usage

```bash
curl --retry 30 --retry-all-errors --retry-delay 2 \
  "http://localhost:8000/api?repo1=symfony/symfony&repo2=laravel/laravel"
```

Requests to GitHub are anonymous, so the GitHub API rate limit applies.

## Test

```bash
docker compose run --rm --no-deps app vendor/bin/phpunit
```

## Override

Keep local changes (e.g. mounting the code into the container) in `compose.override.yml`, which is ignored by git:

```bash
cat > compose.override.yml <<'OVERRIDE'
services:
  app:
    volumes:
      - .:/app
OVERRIDE
docker compose up --build -d --wait
```

## Cleanup

```bash
docker compose down -v --rmi local --remove-orphans
rm -f compose.override.yml
```

## License

This project is licensed under the [PolyForm Noncommercial License 1.0.0](https://polyformproject.org/licenses/noncommercial/1.0.0)
with additional terms (see [LICENSE](LICENSE)). In short: you may read the code and run it to evaluate the
author's job application, but you may not use it commercially, in your company's operations, or as assessment
material in any other hiring process.
