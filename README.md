# **Drupal Spotify Integration**

## **Introduction**

This is a sandbox project for testing the integration of Spotify API into Drupal 10.

This implementaion requires to provide a Spotify Cliend ID and Client Secret key.
These keys are provided by default for testing purposes but you should use your own keys by creating a [Spotify app](https://developer.spotify.com).

There are some blocks and views preconfigured that render content.
Some content is pulled in realtime from Spotify into Drupal for getting up to date top 20 world wide global rank.
Static Artists, Albums, and Songs, are ingested and stored as Drupal entities so can be managed and rendered as standard Drupal content.

## **Setup instructions**

Features pre-configured / pre-installed:

* DDEV yaml file.
* Needed Docker containers (Webserver, MySQL, PHP8, NodeJS).
* Vanilla Drupal 10.
* Drush.
* Composer.
* SCSS pre-processors.

### Step #1: DDEV environment setup

**This is a one time setup - skip this if you already have a working DDEV environment.**

Follow [DDEV environment setup instructions](https://ddev.readthedocs.io/en/stable/users/install/).

Recommendation: on MacOS and Windows, enable the [mutagen](https://ddev.readthedocs.io/en/stable/users/install/performance/) filesystem for a better performance.

### Step #2: Project setup

1. Fork the repo and clone it into your Projects directory.

   ```plaintext
   git clone https://github.com/[username]/spotify.git spotify
   cd spotify
   ```
2. Initialize the DDEV site.

   This will initialize local settings and spin up docker containers.

   ```plaintext
   ddev start
   ```

   You might be asked to introduce your sudo password.
3. Download project dependencies.

   This will download Drupal core and other needed dependencies.

   ```plaintext
   ddev composer install
   ```
4. Deploy Drupal configurations.

   This will run any pending database updates, import all configurations and clear caches in one single command.

   ```plaintext
   ddev drush deploy
   ```
5. Install SCSS node modules (optional).

   If you would like to use SCSS for your styling, install Node dependencies for gulp compiler.

   ```plaintext
   ddev ssh
   cd web/themes/custom/dc
   npm install --location-global gulp-cli
   exit
   ```

   For watching SCSS changes and compiling to CSS run `ddev composer theme:watch`.\
   For compiling SCSS to CSS run `ddev composer theme:build`.
6. Open Drupal website and log in.

   At this point, you should be able to access the working Drupal website at [https://spotify.ddev.site/](https://spotify.ddev.site/) or by running `ddev launch`.\
   Use `admin` for the username and `admin` for the password to log in as an administrator or by running `ddev drush uli`.

### Step #3 - Use

1. Set you own Client ID and Client Secret keys at /admin/config/spotify/settings.
Default values will be provided in case you don't have any.
@TODO: Remove keys from database, store them in a more secure way like using Key module or in environment variables.

2. The Spotify Integration module provides some custom Drush command to ingest content from Spotify API.
- ```ddev drush spotify_integration:artist-albums [spotify-slug]``` Imports all albums for the given spotify artist ID (3WrFJ7ztbogyGnTHbHJFl2 for the Beatles).
- ```ddev drush spotify_integration:artist-top-tracks [spotify-slug]``` Imports top tracks for the given spotify artist ID. (7Ey4PD4MYsKc5I2dolUwbH for Aerosmith).
- ```ddev drush spotify_integration:import-artist-full {[spotify-slug]}``` Imports all albums and tracks for the given spotify artist ID. (7Ey4PD4MYsKc5I2dolUwbH for Aerosmith). Spotify Slug is optional, if skipped, a random artist will be selected.

In general, using the ```spotify_integration:import-artist-full``` drush command is all that you need.
If you want specific artists, then provide the proper slugs as drush command parameters.
If you don't know the slugs or just don't care the artists to import, then run the drush command without parameters.
Run the drush command as many times as artists you want to ingest.
