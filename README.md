# Dev records

Setup a local dev environment for Xianfoodbar
1. Create a Docker Compose setup that runs WordPress + MySQL locally, with the theme mounted in the correct path, developer-friendly env, and writable directories.
2. Frontend assets pre-built in `public/build/` and `public/umi/` — no Node.js needed
3. Setup WordPress admin panel with a restaurant admin account
4. Setup 固定链接设置 in WordPress admin panel. Choose 文章名 and save it.
5. Navigate to the /adminpanel/login page and login with the restaurant admin account

# TODO
## docker-compose.yml
- [ ] Add `chmod 666` for `mytheme/src/logined` in the docker docker-compose.yml entrypoint so the file stays writable after container restarts
