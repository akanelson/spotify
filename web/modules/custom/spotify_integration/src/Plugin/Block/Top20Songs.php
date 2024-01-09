<?php

namespace Drupal\spotify_integration\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Block of top 20 songs.
 *
 * @Block(
 *   id = "top_20_songs_block",
 *   admin_label = @Translation("Top 20 Songs")
 * )
 */
class Top20Songs extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The Spotify Client.
   *
   * @var \Drupal\spotify_integration\SpotifyClient
   */
  protected $spotifyClient;

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
   *   The Spotify Client.
   */
  final public function __construct(array $configuration, $plugin_id, $plugin_definition, $spotify_client) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->spotifyClient = $spotify_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
          $configuration,
          $plugin_id,
          $plugin_definition,
          $container->get('spotify_integration.spotify_client')
      );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $songs = $this->spotifyClient->getTop20Songs();

    return [
      '#theme' => 'top_20_songs',
      '#songs' => ($songs['items']) ?: [],
    ];
  }

}
