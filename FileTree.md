# File Tree: sekkeiShoLrvlAPI

```
├── app
│   ├── Contracts
│   │   └── User
│   │       └── UserExportInterface.php
│   ├── Http
│   │   ├── Controllers
│   │   │   ├── Controller.php
│   │   │   └── UserController.php
│   │   └── Requests
│   │       └── User
│   │           ├── EditUserRequest.php
│   │           ├── RegistUserRequest.php
│   │           └── SearchUserRequest.php
│   ├── Models
│   │   ├── TUser.php
│   │   ├── TUserBelongs.php
│   │   └── TUserDelete.php
│   ├── Providers
│   │   ├── AppServiceProvider.php
│   │   └── RouteServiceProvider.php
│   └── Services
│       └── User
│           ├── UserDeleteService.php
│           ├── UserEditService.php
│           ├── UserExportCsvService.php
│           ├── UserRegisterService.php
│           └── UserSearchService.php
├── bootstrap
│   ├── app.php
│   └── providers.php
├── config
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── cors.php
│   ├── database.php
│   ├── filesystems.php
│   ├── genders.php
│   ├── logging.php
│   ├── mail.php
│   ├── prefectures.php
│   ├── queue.php
│   ├── services.php
│   └── session.php
├── database
│   ├── factories
│   │   ├── TUserBelongsFactory.php
│   │   ├── TUserDeleteFactory.php
│   │   └── TUserFactory.php
│   ├── migrations
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   └── 0001_01_01_000002_create_jobs_table.php
│   ├── seeders
│   │   └── DatabaseSeeder.php
│   └── .gitignore
├── public
│   ├── .htaccess
│   ├── favicon.ico
│   ├── index.php
│   └── robots.txt
├── resources
│   ├── css
│   │   ├── app.css
│   │   └── style.css
│   ├── js
│   │   ├── app.js
│   │   └── bootstrap.js
│   ├── layouts
│   └── views
│       └── layouts
│           └── app.blade.php
├── routes
│   ├── api.php
│   ├── console.php
│   └── web.php
├── storage
│   ├── app
│   │   ├── private
│   │   │   └── .gitignore
│   │   ├── public
│   │   │   └── .gitignore
│   │   └── .gitignore
│   ├── debugbar
│   │   ├── testing
│   │   │   └── .gitignore
│   │   ├── views
│   │   │   ├── .gitignore
│   │   │   ├──
│   │   └── .gitignore
│   └── logs
│       └── .gitignore
├── tests
│   ├── Feature
│   │   ├── UserDeleteTest.php
│   │   ├── UserEditTest.php
│   │   ├── UserExportTest.php
│   │   ├── UserRegisterTest.php
│   │   └── UserSearchTest.php
│   ├── Unit
│   │   └── ExampleTest.php
│   └── TestCase.php
├── .editorconfig
├── .env.example
├── .gitattributes
├── .gitignore
├── Database.txt
├── FileTree.md
├── README.md
├── artisan
├── composer.json
├── package-lock.json
├── package.json
├── phpunit.xml
├── tailwind.config.js
└── vite.config.js
```
