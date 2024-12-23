{ pkgs }: {
  # Which nixpkgs channel to use.
  channel = "stable-24.05"; # or "unstable"

  # Use https://search.nixos.org/packages to find packages
  packages = [
    pkgs.php81
    pkgs.php81Packages.composer
    pkgs.nodejs_20
  ];

  services.mysql = {
    enable = true;
    package = pkgs.mysql84;
  };
  # Sets environment variables in the workspace
  env = {
    # APP_ENV = "development";
    # APP_KEY = "base64:your_app_key_here";
    DB_CONNECTION = "mysql";
    DB_HOST = "127.0.0.1";
    DB_PORT = "3306";
    DB_DATABASE = "example_app";
    # DB_USERNAME = "your_username";

  };

  idx = {
    # Search for the extensions you want on https://open-vsx.org/ and use "publisher.id"
    extensions = [
      "mhutchie.git-graph"
      "cweijan.vscode-mysql-client2"
      # Uncomment or add extensions as needed
      # "vscodevim.vim"
      # "bmewburn.vscode-intelephense-client" # PHP IntelliSense
      # "esbenp.prettier-vscode"             # Prettier for JS/TS formatting
    ];

    workspace = {
      # Runs when a workspace is first created with this `dev.nix` file
      onCreate = {

        copyEnvFile = "cp InitialProject/src/.env.example InitialProject/src/.env";

        # Install PHP dependencies using Composer
        composerInstall = "cd InitialProject/src && composer install";

        # Install Node.js dependencies using npm
        # npmInstall = "cd InitialProject && npm install";

        # Open specific files by default
        default.openFiles = [ "README.md"];
      };

            # To run something each time the workspace is (re)started, use the `onStart` hook
      onStart = {
        
        # copyEnvFile = "cp InitialProject/src/.env.example InitialProject/src/.env";

        # Start MySQL server and create the database
        mysqlStart = ''
          mysql -u root -e "CREATE DATABASE IF NOT EXISTS example_app;"
        '';


        generateAppKey = "cd InitialProject/src && php artisan key:generate --force";

        # Run database migrations
        dbMigrate = "cd InitialProject/src && php artisan migrate --force";

      };
    };

    # Enable previews and customize configuration
    previews = {
      enable = true;
      previews = {
        web = {
          command = [ "sh" "-c" "cd InitialProject/src && php artisan serve --port $PORT --host 0.0.0.0" ];
          manager = "web";
        };
      };
    };
  };
}
