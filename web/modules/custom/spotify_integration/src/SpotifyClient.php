<?php

namespace Drupal\spotify_integration;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Http\ClientFactory;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * The Spotify Client class.
 */
class SpotifyClient {

  /**
   * The HTTP Client service.
   *
   * @var \GuzzleHttp\Client
   */
  protected $spotifyClient;

  /**
   * The access token.
   *
   * @var string
   */
  protected $accessToken;

  /**
   * The config service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The logger factory service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * SpotifyClient constructor.
   *
   * @param \Drupal\Core\Http\ClientFactory $http_client_factory
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   The config factory service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The config factory service.
   */
  public function __construct(ClientFactory $http_client_factory, ConfigFactory $config, LoggerChannelFactoryInterface $logger_factory) {
    $this->spotifyClient = $http_client_factory->fromOptions(
          [
            'base_uri' => 'https://api.spotify.com/v1/',
          ]
      );
    $this->configFactory = $config;
    $this->loggerFactory = $logger_factory;

    // Get an Access Token.
    $this->authorize();
  }

  /**
   * Get API Token.
   *
   * @return array
   *   The array returned.
   */
  public function authorize() {
    try {
      $config = $this->configFactory->get('spotify_integration.spotify_settings');
      $client_id = ($config->get('client_id')) ?: '2038e797c7a2431a85571b047d3cf316';
      $client_secret = ($config->get('client_secret')) ?: '4ca1ecef811d41f5978002dbd051dc46';

      $response = $this->spotifyClient->post(
            'https://accounts.spotify.com/api/token', [
              'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic ' . base64_encode($client_id . ':' . $client_secret),
              ],
              'form_params' => [
                'grant_type' => 'client_credentials',
              ],
            ]
        );

      $data = Json::decode($response->getBody());

      $this->accessToken = $data['access_token'];

      return $data;
    }
    catch (RequestException $exception) {
      $this->loggerFactory->get('spotify_integration')->error('Authorize error @error.', ['@error' => $exception->getMessage()]);
      return [];
    }
  }

  /**
   * Get top 20 songs.
   *
   * (from global top 50 songs playlist '37i9dQZEVXbMDoHDwVN2tF').
   *
   * @return array
   *   The returned array.
   */
  public function getTop20Songs() {
    try {
      $response = $this->spotifyClient->get(
        'playlists/37i9dQZEVXbMDoHDwVN2tF/tracks', [
          'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->accessToken,
          ],
          'query' => [
            'limit' => 20,
            'fields' => 'items(track(id,name,album(images)))',
          ],
        ]
      );

      $data = Json::decode($response->getBody());

      return $data;
    }
    catch (RequestException $exception) {
      $this->loggerFactory->get('spotify_integration')->error('GetTop20Songs error @error.', ['@error' => $exception->getMessage()]);
      return [];
    }

  }

  /**
   * Get 10 random artists.
   *
   * @return array
   *   The returned array.
   */
  public function getRandom10Artists() {
    try {
      // Get random character.
      $char = chr(rand(65, 90));

      // Get random offset.
      $offset = rand(0, 100);

      $response = $this->spotifyClient->get(
            'search', [
              'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->accessToken,
              ],
              'query' => [
                'limit' => 10,
                'offset' => $offset,
                'q' => $char,
                'type' => 'artist',
              ],
            ]
        );

      $data = Json::decode($response->getBody());
      $data['random_char'] = $char;

      return $data;
    }
    catch (RequestException $exception) {
      $this->loggerFactory->get('spotify_integration')->error('GetRandom10Artists error @error.', ['@error' => $exception->getMessage()]);
      return [];
    }

  }

  /**
   * Get All Artists (randomly and limited to 50).
   *
   * @param string $char
   *   The initial character.
   *
   * @return array
   *   The returned array.
   */
  public function getAllArtists(String $char) {
    try {
      $response = $this->spotifyClient->get(
        'search', [
          'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->accessToken,
          ],
          'query' => [
            'limit' => 50,
            'q' => $char,
            'type' => 'artist',
          ],
        ]
      );

      $data = Json::decode($response->getBody());

      return $data;
    }
    catch (RequestException $exception) {
      $this->loggerFactory->get('spotify_integration')->error('GetAllArtists error @error.', ['@error' => $exception->getMessage()]);
      return [];
    }

  }

  /**
   * Get num Artists randomly.
   *
   * @param int $num
   *   The limit of artist to get.
   *
   * @return array
   *   The returned array.
   */
  public function getArtists(Int $num) {
    try {
      // Set max to 50 artists.
      if ($num > 50) {
        $num = 50;
      }

      // Get random character.
      $char = chr(rand(65, 90));

      // Get random offset.
      $offset = rand(0, 100);

      $response = $this->spotifyClient->get(
            'search', [
              'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->accessToken,
              ],
              'query' => [
                'limit' => $num,
                'offset' => $offset,
                'q' => $char,
                'type' => 'artist',
              ],
            ]
      );

      $data = Json::decode($response->getBody());

      return $data;
    }
    catch (RequestException $exception) {
      $this->loggerFactory->get('spotify_integration')->error('GetArtists error @error.', ['@error' => $exception->getMessage()]);
      return [];
    }
  }

  /**
   * Get artist's albums.
   *
   * @param string $artist_id
   *   The artist id.
   *
   * @return array
   *   The returned array.
   */
  public function getArtistAlbums(String $artist_id) {
    try {
      $response = $this->spotifyClient->get(
        'artists/' . $artist_id . '/albums', [
          'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->accessToken,
          ],
          'query' => [
            'limit' => 50,
          ],
        ]
      );

      $data = Json::decode($response->getBody());

      return $data;
    }
    catch (RequestException $exception) {
      $this->loggerFactory->get('spotify_integration')->error('GetArtistAlbums error @error.', ['@error' => $exception->getMessage()]);
      return [];
    }
  }

  /**
   * Get artist's top tracks.
   *
   * @param string $artist_id
   *   The artist id.
   *
   * @return array
   *   The returned array.
   */
  public function getArtistTopTracks(String $artist_id) {
    try {
      $response = $this->spotifyClient->get(
        'artists/' . $artist_id . '/top-tracks', [
          'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->accessToken,
          ],
          'query' => [
            'market' => 'us',
            'limit' => 10,
          ],
        ]
      );

      $data = Json::decode($response->getBody());

      return $data;
    }
    catch (RequestException $exception) {
      $this->loggerFactory->get('spotify_integration')->error('GetArtistTopTracks error @error.', ['@error' => $exception->getMessage()]);
      return [];
    }
  }

  /**
   * Get Album's tracks.
   *
   * @param string $album_id
   *   The album id.
   *
   * @return array
   *   The returned array.
   */
  public function getAlbum(String $album_id) {
    try {
      $response = $this->spotifyClient->get(
        'albums/' . $album_id, [
          'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->accessToken,
          ],
          'query' => [
            'limit' => 50,
          ],
        ]
      );

      $data = Json::decode($response->getBody());

      return $data;
    }
    catch (RequestException $exception) {
      $this->loggerFactory->get('spotify_integration')->error('GetAlbum error @error.', ['@error' => $exception->getMessage()]);
      return [];
    }
  }

  /**
   * Get an Artist.
   *
   * @param string $artist_id
   *   The artist id.
   *
   * @return array
   *   The returned array.
   */
  public function getArtist(String $artist_id) {
    try {
      $response = $this->spotifyClient->get(
        'artists/' . $artist_id, [
          'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->accessToken,
          ],
        ]
      );

      $data = Json::decode($response->getBody());

      return $data;
    }
    catch (RequestException $exception) {
      $this->loggerFactory->get('spotify_integration')->error('GetArtist error @error.', ['@error' => $exception->getMessage()]);
      return [];
    }
  }

  /**
   * Get a Track.
   *
   * @param string $track_id
   *   The track id.
   *
   * @return array
   *   The returned array.
   */
  public function getTrack(String $track_id) {
    try {
      $response = $this->spotifyClient->get(
        'tracks/' . $track_id, [
          'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->accessToken,
          ],
        ]
      );

      $data = Json::decode($response->getBody());

      return $data;
    }
    catch (RequestException $exception) {
      $this->loggerFactory->get('spotify_integration')->error('GetTrack error @error.', ['@error' => $exception->getMessage()]);
      return [];
    }
  }

}
