# Recruitment Task: Volt

[![CI](https://github.com/gander/Recruitment-Task__Volt/actions/workflows/ci.yml/badge.svg)](https://github.com/gander/Recruitment-Task__Volt/actions/workflows/ci.yml)

Zadanie: API w Symfony porównujące dwa repozytoria GitHub. Endpoint `GET /api?repo1=owner/name&repo2=owner/name` pobiera z API GitHuba liczbę gwiazdek, forków, obserwujących i pull requestów obu repozytoriów (nazwy są walidowane), a następnie zwraca wynik porównania jako JSON.

## Requirements

- Docker Engine z Docker Compose v2 (jedyna zależność; PHP ani Composer na hoście nie są potrzebne).
- `curl` do przykładów użycia.

## Install

```bash
docker compose up --build -d --wait
```

## Usage

```bash
curl --retry 30 --retry-all-errors --retry-delay 2 \
  "http://localhost:8000/api?repo1=symfony/symfony&repo2=laravel/laravel"
```

Zapytania do GitHuba są anonimowe, więc obowiązuje limit API GitHuba.

## Test

```bash
docker compose run --rm --no-deps app vendor/bin/phpunit
```

## Override

Lokalne zmiany (np. montowanie kodu do kontenera) trzymaj w `compose.override.yml`, ignorowanym przez git:

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
