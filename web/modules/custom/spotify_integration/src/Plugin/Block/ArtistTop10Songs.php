<?php

namespace Drupal\spotify_integration\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\Entity\NodeType;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Block of top 20 songs.
 *
 * @Block(
 *   id = "artist_top_10_songs_block",
 *   admin_label = @Translation("Artist Top 10 Songs")
 * )
 */
class ArtistTop10Songs extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The Spotify Client.
   *
   * @var \Drupal\spotify_integration\SpotifyClient
   */
  protected $spotifyClient;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $routeMatchClient;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestClient;

  /**
   * Top20Songs constructor.
   *
   * @param array $configuration
   *   The configuration variable.
   * @param string $plugin_id
   *   The plugin id variable.
   * @param string $plugin_definition
   *   The plugin definition variable.
   * @param \Drupal\spotify_integration\SpotifyClient $spotify_client
   *   The Spotify client.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $route_match_client
   *   The route match client service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_client
   *   The request client service.
   */
  final public function __construct(array $configuration, $plugin_id, $plugin_definition, $spotify_client, $route_match_client, $request_client) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->spotifyClient = $spotify_client;
    $this->routeMatchClient = $route_match_client;
    $this->requestClient = $request_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
          $configuration,
          $plugin_id,
          $plugin_definition,
          $container->get('spotify_integration.spotify_client'),
          $container->get('current_route_match'),
          $container->get('request_stack')
      );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = $this->routeMatchClient->getParameter('node');
    if ($node instanceof NodeType) {
      $slug = $node->field_spotify_slug->value;
      $songs = $this->spotifyClient->getArtistTopTracks($slug);
      $songs = $songs['tracks'];
    }
    else {
      $songs = [];
    }

    return [
      '#theme' => 'artist_top_10_songs',
      '#songs' => $songs,
    ];
  }

}
