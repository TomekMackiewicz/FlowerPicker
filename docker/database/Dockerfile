FROM postgres:latest
EXPOSE 5432

RUN chmod -R 775 ./docker-entrypoint-initdb.d

COPY ./init-sql/create-multiple-postgresql-databases.sh /docker-entrypoint-initdb.d/