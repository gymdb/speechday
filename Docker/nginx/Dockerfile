FROM nginx:alpine

COPY entrypoint.sh /usr/local/bin
RUN ["chmod", "+x", "/usr/local/bin/entrypoint.sh"]
RUN ln -s usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["nginx"]

EXPOSE 80 443
