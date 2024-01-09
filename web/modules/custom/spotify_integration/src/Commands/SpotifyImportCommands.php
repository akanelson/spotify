<?php

namespace Drupal\spotify_integration\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\spotify_integration\SpotifyClient;
use Drush\Commands\DrushCommands;

/**
 * A drush command file.
 *
 * @package Drupal\spotify_integration\Commands
 */
class SpotifyImportCommands extends DrushCommands {

  /**
   * The Spotify Client.
   *
   * @var \Drupal\spotify_integration\SpotifyClient
   */
  protected $spotifyClient;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Spotify_integrationSpotifyClient constructor.
   *
   * @param \Drupal\spotify_integration\SpotifyClient $spotify_client
   *   The Spotify client.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(SpotifyClient $spotify_client, EntityTypeManagerInterface $entity_type_manager) {
    $this->spotifyClient = $spotify_client;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Drush command that returns artists with a given starting character.
   *
   * @param string $char
   *   The initial char.
   *
   * @command spotify_integration:all-artists
   * @aliases spotify_integration-all-artists
   * @usage spotify_integration:all-artists A
   */
  public function getAllArtists($char = 'A') {
    $this->output()->writeln(print_r($this->spotifyClient->getAllArtists($char), TRUE));
  }

  /**
   * Drush command that returns artist's albums.
   *
   * @param string $artist_id
   *   The artist id.
   *
   * @command spotify_integration:artist-albums
   * @aliases spotify_integration-artist-albums
   * @usage spotify_integration:artist-albums 3WrFJ7ztbogyGnTHbHJFl2 (The Beatles)
   */
  public function getArtistAlbums($artist_id) {
    $this->output()->writeln(print_r($this->spotifyClient->getArtistAlbums($artist_id), TRUE));
  }

  /**
   * Drush command that returns album's tracks.
   *
   * @param string $album_id
   *   The album id.
   *
   * @command spotify_integration:album-tracks
   * @aliases spotify_integration-album-tracks
   * @usage spotify_integration:album-tracks 1pBBIxK5yURfbv8Xd5lta1 (The Beatles - Anthology I)
   */
  public function getAlbumTracks($album_id) {
    // @todo build this method if needed.
    // $this->output()->
    // writeln(print_r($this->spotifyClient->getAlbumTracks($album_id), true));
  }

  /**
   * Drush command that returns artist's top tracks.
   *
   * @param string $artist_id
   *   The artist id.
   *
   * @command spotify_integration:artist-top-tracks
   * @aliases spotify_integration-artist-top-tracks
   * @usage spotify_integration:artist-top-tracks 7Ey4PD4MYsKc5I2dolUwbH (Aerosmith US Market)
   */
  public function getArtistTopTracks($artist_id) {
    $this->output()->writeln(print_r($this->spotifyClient->getArtistTopTracks($artist_id), TRUE));
  }

  /**
   * Drush command that imports an artist with all albums and songs.
   *
   * @param string $artist_id
   *   The artist id.
   *
   * @command spotify_integration:import-artist-full
   * @aliases import-artist-full iaf
   * @usage spotify_integration:import-artist-full 3WrFJ7ztbogyGnTHbHJFl2 (The Beatles)
   */
  public function importArtistWithAlbumsAndSongs($artist_id = NULL) {
    // Get an artist.
    if ($artist_id) {
      // Get the artist.
      $artist = $this->spotifyClient->getArtist($artist_id);
    }
    else {
      // Get one random artist.
      $artist = $this->spotifyClient->getArtists(1);
      // Extracts first artist item from the array.
      $artist = $artist['artists']['items'][0];
    }

    // Artist Genres.
    $this->output()->writeln(' ======= STARTING IMPORT. CANCEL AT ANY TIME WITH CTRL+C ======');
    $this->output()->writeln('');

    $this->output()->writeln(' ======= ARTIST GENRES ======');
    $this->output()->writeln('');

    $artist_genres = [];
    $termStorage = $this->entityTypeManager->getStorage('taxonomy_term');
    foreach ($artist['genres'] as $artist_genre) {
      /* var Drupal\taxonomy\Entity\Term $genre */
      $genre = $termStorage->loadByProperties(['name' => $artist_genre, 'vid' => 'genres']);
      $genre = reset($genre);
      if ($genre) {
        $this->output()->writeln('Genre "' . $artist_genre . '" already existing with id ' . $genre->id() . ' -- Skipping.');
        $artist_genres[] = $genre->id();
      }
      else {
        $this->output()->write('Creating new genre "' . $artist_genre . '" ...');
        $new_genre = $termStorage->create(
              [
                'name' => $artist_genre,
                'vid' => 'genres',
              ]
          );
        $new_genre->save();
        $this->output()->writeln(' -- New genre created with id ' . $new_genre->id() . '.');
        $artist_genres[] = $new_genre->id();
      }
    }

    // Artist.
    $this->output()->writeln('');
    $this->output()->writeln(' ======= ARTIST ======');
    $this->output()->writeln('');

    $nodeStorage = $this->entityTypeManager->getStorage('node');
    $existing_artist = $nodeStorage->loadByProperties(['field_spotify_slug' => $artist['id'], 'type' => 'artists']);
    $existing_artist = reset($existing_artist);
    if ($existing_artist) {
      $drupal_artist_id = $existing_artist->id();
      $this->output()->writeln('Artist "' . $artist['name'] . '" already existing with id ' . $drupal_artist_id . ' -- Skipping.');
    }
    else {
      $this->output()->write('Creating new artist "' . $artist['name'] . '" ...');

      $new_artist = $nodeStorage->create(
            [
              'type' => 'artists',
              'title' => $artist['name'],
              'field_followers' => $artist['followers']['total'],
              'field_image' => ($artist['images'][0]) ? $artist['images'][0]['url'] : '/themes/dc/assets/images/spotify-no-image.png',
              'field_spotify_link' => $artist['external_urls']['spotify'],
              'field_spotify_slug' => $artist['id'],
              'field_genres' => $artist_genres,
              'uid' => 1,
            ]
        );

      $new_artist->save();
      $drupal_artist_id = $new_artist->id();
      $this->output()->writeln(' -- New artist created with id ' . $drupal_artist_id . '.');
    }

    // Albums.
    // Get all albums from the artist.
    $albums = $this->spotifyClient->getArtistAlbums($artist['id']);

    foreach ($albums['items'] as $album) {
      // Get only album types.
      if ($album['album_type'] == 'album') {
        $this->output()->writeln('');
        $this->output()->writeln(' ======= ALBUM ======');
        $this->output()->writeln('');

        $existing_album = $nodeStorage->loadByProperties(['field_spotify_slug' => $album['id'], 'type' => 'albums']);
        $existing_album = reset($existing_album);

        // Some times there are different albums IDs corresponding
        // to the same album but for different markets.
        // Checking if duplicated album (by name) already exists for
        // the same artist and skipping importing and avoid confusions.
        $duplicated_artis_album_name = $nodeStorage->loadByProperties([
          'title' => $album['name'],
          'field_artist' => $drupal_artist_id,
          'type' => 'albums',
        ]);
        $drupal_album_id = NULL;
        if ($existing_album || $duplicated_artis_album_name) {
          if ($existing_album) {
            $drupal_album_id = $existing_album->id();
            $this->output()->writeln('Album "' . $album['name'] . '" already existing with id ' . $drupal_album_id . ' -- Skipping.');
          }
          if ($duplicated_artis_album_name) {
            $this->output()->writeln(' === DUPLICATED ALBUM FOR ARTIST === Album name "' . $album['name'] . '" duplicated for the Artist "' . $artist['name'] . '". Skipping.');
          }
        }
        else {
          $this->output()->write('Creating new album "' . $album['name'] . '" ...');

          $new_album = $nodeStorage->create(
                [
                  'type' => 'albums',
                  'title' => $album['name'],
                  'field_artist' => ['target_id' => $drupal_artist_id],
                  'field_release_date' => $album['release_date'],
                  'field_image' => ($album['images'][0]) ? $album['images'][0]['url'] : '/themes/dc/assets/images/spotify-no-image.png',
                  'field_spotify_link' => $album['external_urls']['spotify'],
                  'field_spotify_slug' => $album['id'],
                  'uid' => 1,
                ]
            );

          $new_album->save();
          $drupal_album_id = $new_album->id();
          $this->output()->writeln(' -- New album created with id ' . $drupal_album_id . '.');
        }

        // Songs.
        // If the album whas skipped due to a duplication name,
        // skip also their songs.
        if (!$duplicated_artis_album_name) {
          $this->output()->writeln('');
          $this->output()->writeln(' ======= Songs ======');
          $this->output()->writeln('');

          // Get all tracks from the album.
          $album_tracks = $this->spotifyClient->getAlbum($album['id']);
          foreach ($album_tracks['tracks']['items'] as $track) {
            // Get song info.
            $song = $this->spotifyClient->getTrack($track['id']);

            $existing_songs = $nodeStorage->loadByProperties(['field_spotify_slug' => $track['id'], 'type' => 'songs']);
            $existing_songs = reset($existing_songs);
            if ($existing_songs) {
              $drupal_song_id = $existing_songs->id();
              $this->output()->writeln('Song "' . $song['name'] . '" already existing with id ' . $drupal_song_id . ' -- Skipping.');
            }
            else {
              $this->output()->write('Creating new song "' . $song['name'] . '" ...');

              $new_song = $nodeStorage->create(
                    [
                      'type' => 'songs',
                      'title' => $song['name'],
                      'field_album' => ['target_id' => $drupal_album_id],
                      'field_spotify_link' => $song['external_urls']['spotify'],
                      'field_spotify_slug' => $song['id'],
                      'field_spotify_popularity' => $song['popularity'],
                      'uid' => 1,
                    ]
                );

              $new_song->save();
              $drupal_song_id = $new_song->id();
              $this->output()->writeln(' -- New song created with id ' . $drupal_song_id . '.');
            }
          }
        }
      }
    }
  }

}
