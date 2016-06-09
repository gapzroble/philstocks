set :application, "pse-tool"
set :user, "ubuntu"
set :domain,      "freetier2"
set :deploy_to,   "/var/www/#{application}"
set :app_path,    "app"

set :repository,  "git@github.com:gapzroble/pse-tool.git"
set :branch, "master"
set :scm,         :git
# Or: `accurev`, `bzr`, `cvs`, `darcs`, `subversion`, `mercurial`, `perforce`, or `none`

set :model_manager, "doctrine"
# Or: `propel`

role :web,        domain                         # Your HTTP server, Apache/etc
role :app,        domain, :primary => true       # This may be the same as your `Web` server

set  :keep_releases,  1

# Be more verbose by uncommenting the following line
logger.level = Logger::MAX_LEVEL

set :deploy_via,      :rsync_with_remote_cache
set :shared_files,    ["app/config/parameters.yml"]
set :shared_children, ["vendor", "var/logs"]
set :use_composer,    true
set :use_sudo,        false

set :log_path, "var/logs"
set :cache_path, "var/cache"
set :symfony_console, "bin/console"
set :symfony_vendors,       "vendors"
set :composer_bin,          "/usr/local/bin/composer"

set :writable_dirs,       [cache_path, log_path]
set :permission_method,   :acl
set :use_set_permissions, true

after "deploy", "deploy:cleanup"
