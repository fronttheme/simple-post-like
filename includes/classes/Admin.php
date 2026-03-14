<?php

  namespace FrontTheme\SimplePostLike;

  use FrontTheme\SimplePostLike\Traits\Singleton;

  defined( 'ABSPATH' ) || exit;

  /**
   * Registers and renders the plugin admin page.
   *
   * Tabs:
   *  - Settings   → display, post types, guests, injection placement.
   *  - Statistics → total likes, most liked posts.
   */
  class Admin {
    use Singleton;

    protected function init(): void {
      add_action( 'admin_menu', [ $this, 'register_menu' ] );
      add_action( 'admin_post_spl_save_settings', [ $this, 'handle_save' ] );
      add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
      add_action('admin_head', [$this, 'clean_admin_page'], 999); // High priority
    }

    /**
     * Register the admin menu page under Settings.
     */
    public function register_menu(): void {
      add_options_page(
          esc_html__( 'Simple Post Like', 'simple-post-like' ),
          esc_html__( 'Simple Post Like', 'simple-post-like' ),
          'manage_options',
          'simple-post-like',
          [ $this, 'render_page' ]
      );
    }

    /**
     * Enqueue admin-only assets (only on our page).
     */
    public function enqueue_admin_assets( string $hook ): void {
      if ( $hook !== 'settings_page_simple-post-like' ) {
        return;
      }

      wp_enqueue_style(
          'simple-post-like-admin-css',
          SIMPLE_POST_LIKE_URL . 'assets/css/admin.css',
          [],
          SIMPLE_POST_LIKE_VERSION
      );

      wp_enqueue_script(
          'simple-post-like-admin-js',
          SIMPLE_POST_LIKE_URL . 'assets/js/admin.js',
          [],
          SIMPLE_POST_LIKE_VERSION,
          true
      );
    }

    /**
     * Handle settings form POST via admin-post.php.
     */
    public function handle_save(): void {
      if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have permission to do this.', 'simple-post-like' ) );
      }

      check_admin_referer( 'spl_save_settings', 'spl_nonce' );

      Settings::instance()->save( [
          'display_type'   => $_POST['display_type'] ?? '',
          'post_types'     => $_POST['post_types'] ?? [],
          'allow_guests'   => isset( $_POST['allow_guests'] ),
          'auto_inject'    => isset( $_POST['auto_inject'] ),
          'inject_single'  => $_POST['inject_single'] ?? 'after',
          'inject_archive' => isset( $_POST['inject_archive'] ),
      ] );

      // Set a transient that expires in 30 seconds
      set_transient( 'spl_settings_saved', true, 30 );

      wp_redirect( add_query_arg( [
          'page' => 'simple-post-like',
          'tab'  => 'settings',
      ], admin_url( 'options-general.php' ) ) );
      exit;
    }

    /**
     * Master render — resolves active tab then delegates.
     */
    public function render_page(): void {
      if ( ! current_user_can( 'manage_options' ) ) {
        return;
      }

      $active_tab   = ( isset( $_GET['tab'] ) && $_GET['tab'] === 'statistics' ) ? 'statistics' : 'settings';
      $saved_notice = ( $active_tab === 'settings' ) && ! empty( $_GET['spl-updated'] );
      $settings_url = admin_url( 'options-general.php' );
      ?>
      <div class="wrap spl-wrap">

        <!-- Header -->
        <div class="spl-header">
          <div class="spl-header__brand">
            <div class="spl-header__logo">
              <img src="<?php echo esc_url( SIMPLE_POST_LIKE_URL . 'assets/images/simple-post-like-icon.png' ); ?>"
                   alt="<?php esc_attr_e( 'Simple Post Like Logo', 'simple-post-like' ); ?>"
                   class="spl-header__logo-img">
              <h1 class="spl-header__title">
                <?php esc_html_e( 'Simple Post Like', 'simple-post-like' ); ?>
                <span class="spl-header__version">v<?php echo esc_html( SIMPLE_POST_LIKE_VERSION ); ?></span>
              </h1>
            </div>
            <p class="spl-header__desc"><?php esc_html_e( 'Add simple, intuitive reactions to your posts.', 'simple-post-like' ); ?></p>
          </div>
          <a class="spl-header__link"
             href="https://www.fronttheme.com/products/simple-post-like"
             target="_blank" rel="noopener noreferrer">
            <?php esc_html_e( 'Documentation', 'simple-post-like' ); ?>
            <svg class="spl-icon-external" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
              <path d="M15 3h6v6"/>
              <path d="M10 14 21 3"/>
              <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
            </svg>
          </a>
        </div>

        <!-- Tab Nav -->
        <nav class="spl-tabs" aria-label="<?php esc_attr_e( 'Plugin sections', 'simple-post-like' ); ?>">
          <a class="spl-tabs__item <?php echo $active_tab === 'settings' ? 'is-active' : ''; ?>"
             href="<?php echo esc_url( add_query_arg( [
                 'page' => 'simple-post-like',
                 'tab'  => 'settings'
             ], $settings_url ) ); ?>">
            <span class="dashicons dashicons-admin-settings"></span>
            <?php esc_html_e( 'Settings', 'simple-post-like' ); ?>
          </a>
          <a class="spl-tabs__item <?php echo $active_tab === 'statistics' ? 'is-active' : ''; ?>"
             href="<?php echo esc_url( add_query_arg( [
                 'page' => 'simple-post-like',
                 'tab'  => 'statistics'
             ], $settings_url ) ); ?>">
            <span class="dashicons dashicons-chart-bar"></span>
            <?php esc_html_e( 'Statistics', 'simple-post-like' ); ?>
          </a>
        </nav>

        <!-- Tab Content -->
        <div class="spl-tab-content">
          <?php if ( $active_tab === 'settings' ) : ?>
            <?php $this->render_settings_tab( $saved_notice ); ?>
          <?php else : ?>
            <?php $this->render_statistics_tab(); ?>
          <?php endif; ?>
        </div>

        <div class="spl-admin__footer">
          <p class="spl-footer__copyright">
            &copy; <?php echo date( 'Y' ); ?>
            <a href="https://www.fronttheme.com" target="_blank" rel="noopener noreferrer">FrontTheme</a>
          </p>
          <a href="https://www.fronttheme.com" target="_blank" rel="noopener noreferrer" class="spl-footer__logo-link">
            <img src="<?php echo esc_url( SIMPLE_POST_LIKE_URL . 'assets/images/fronttheme-logo-light.svg' ); ?>"
                 alt="FrontTheme"
                 class="spl-footer__logo-image">
          </a>
        </div>

      </div>
      <?php
    }

    /* ------------------------------------------------------------------ */
    /* Settings Tab                                                         */
    /* ------------------------------------------------------------------ */

    private function render_settings_tab( bool $saved_notice ): void {
      $settings = Settings::instance()->all();
      $all_pt   = $this->get_public_post_types();

      // Check if transient exists (meaning we just saved)
      $saved_notice = get_transient( 'spl_settings_saved' );

      // Delete the transient so it doesn't show again
      if ( $saved_notice ) {
        delete_transient( 'spl_settings_saved' );
      }
      ?>

      <?php if ( $saved_notice ) : ?>
        <div class="notice notice-success is-dismissible spl-notice">
          <p><?php esc_html_e( 'Settings saved successfully.', 'simple-post-like' ); ?></p>
        </div>
      <?php endif; ?>

      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="spl_save_settings">
        <?php wp_nonce_field( 'spl_save_settings', 'spl_nonce' ); ?>

        <!-- Section: Button -->
        <div class="spl-section">
          <div class="spl-section__head">
            <h2 class="spl-section__title"><?php esc_html_e( 'Button', 'simple-post-like' ); ?></h2>
            <p class="spl-section__desc">
              <?php esc_html_e( 'Control how the like button looks and who can use it.', 'simple-post-like' ); ?>
            </p>
          </div>

          <table class="form-table spl-form-table" role="presentation">

            <!-- Display Style -->
            <tr>
              <th scope="row"><?php esc_html_e( 'Display Style', 'simple-post-like' ); ?></th>
              <td>
                <div class="spl-radio-group">
                  <?php
                    $display_options = [
                        'button_default' => [
                            'label' => __( 'Button', 'simple-post-like' ),
                            'desc'  => __( 'Icon + label + count', 'simple-post-like' ),
                        ],
                        'icon_counter'   => [
                            'label' => __( 'Icon + Counter', 'simple-post-like' ),
                            'desc'  => __( 'Icon with number beside it', 'simple-post-like' ),
                        ],
                        'icon_only'      => [
                            'label' => __( 'Icon Only', 'simple-post-like' ),
                            'desc'  => __( 'Just the heart icon', 'simple-post-like' ),
                        ],
                    ];
                    foreach ( $display_options as $value => $opt ) : ?>
                      <label class="spl-radio-card <?php echo $settings['display_type'] === $value ? 'is-selected' : ''; ?>">
                        <input type="radio" name="display_type"
                               value="<?php echo esc_attr( $value ); ?>"
                            <?php checked( $settings['display_type'], $value ); ?>>
                        <span class="spl-radio-card__label"><?php echo esc_html( $opt['label'] ); ?></span>
                        <span class="spl-radio-card__desc"><?php echo esc_html( $opt['desc'] ); ?></span>
                      </label>
                    <?php endforeach; ?>
                </div>
              </td>
            </tr>

            <!-- Post Types -->
            <tr>
              <th scope="row"><?php esc_html_e( 'Enable on Post Types', 'simple-post-like' ); ?></th>
              <td>
                <div class="spl-checkbox-group">
                  <?php foreach ( $all_pt as $pt_slug => $pt_label ) : ?>
                    <label class="spl-checkbox-item">
                      <input class="spl-checkbox" type="checkbox" name="post_types[]"
                             value="<?php echo esc_attr( $pt_slug ); ?>"
                          <?php checked( in_array( $pt_slug, $settings['post_types'], true ) ); ?>>
                      <span class="spl-checkbox-item__label"><?php echo esc_html( $pt_label ); ?></span>
                      <code class="spl-checkbox-item__slug"><?php echo esc_html( $pt_slug ); ?></code>
                    </label>
                  <?php endforeach; ?>
                </div>
                <p class="description">
                  <?php esc_html_e( 'Choose which post types display the like button.', 'simple-post-like' ); ?>
                </p>
              </td>
            </tr>

            <!-- Allow Guests -->
            <tr>
              <th scope="row">
                <label for="allow_guests"><?php esc_html_e( 'Allow Guest Likes', 'simple-post-like' ); ?></label>
              </th>
              <td>
                <label class="spl-toggle">
                  <input type="checkbox" id="allow_guests" name="allow_guests" value="1"
                      <?php checked( $settings['allow_guests'] ); ?>>
                  <span class="spl-toggle__slider"></span>
                  <span class="spl-toggle__label">
                  <?php esc_html_e( 'Allow non-logged-in visitors to like posts (tracked by hashed IP).', 'simple-post-like' ); ?>
                </span>
                </label>
              </td>
            </tr>

          </table>
        </div>

        <!-- Section: Auto Injection -->
        <div class="spl-section">
          <div class="spl-section__head">
            <h2 class="spl-section__title"><?php esc_html_e( 'Auto Injection', 'simple-post-like' ); ?></h2>
            <p class="spl-section__desc">
              <?php esc_html_e( 'Control where the like button appears automatically. Disable the master toggle to switch to shortcode-only mode.', 'simple-post-like' ); ?>
            </p>
          </div>

          <table class="form-table spl-form-table" role="presentation">

            <!-- Master Toggle -->
            <tr>
              <th scope="row">
                <label for="auto_inject"><?php esc_html_e( 'Enable Auto Injection', 'simple-post-like' ); ?></label>
              </th>
              <td>
                <label class="spl-toggle">
                  <input type="checkbox" id="auto_inject" name="auto_inject" value="1"
                      <?php checked( $settings['auto_inject'] ); ?>>
                  <span class="spl-toggle__slider"></span>
                  <span class="spl-toggle__label">
                  <?php esc_html_e( 'Automatically output the like button without editing templates.', 'simple-post-like' ); ?>
                </span>
                </label>
              </td>
            </tr>

            <!-- Single Post Position -->
            <tr>
              <th scope="row"><?php esc_html_e( 'Single Post Position', 'simple-post-like' ); ?></th>
              <td>
                <div class="spl-radio-group spl-radio-group--pills">
                  <?php
                    $single_options = [
                        'after'  => __( 'After content', 'simple-post-like' ),
                        'before' => __( 'Before content', 'simple-post-like' ),
                        'both'   => __( 'Before &amp; after', 'simple-post-like' ),
                        'none'   => __( 'Do not inject', 'simple-post-like' ),
                    ];
                    foreach ( $single_options as $value => $label ) : ?>
                      <label class="spl-radio-pill <?php echo $settings['inject_single'] === $value ? 'is-selected' : ''; ?>">
                        <input type="radio" name="inject_single"
                               value="<?php echo esc_attr( $value ); ?>"
                            <?php checked( $settings['inject_single'], $value ); ?>>
                        <?php echo $label; ?>
                      </label>
                    <?php endforeach; ?>
                </div>
                <p class="description">
                  <?php esc_html_e( 'Where to place the button on individual post pages.', 'simple-post-like' ); ?>
                </p>
              </td>
            </tr>

            <!-- Archive Injection -->
            <tr>
              <th scope="row">
                <label for="inject_archive"><?php esc_html_e( 'Inject on Archive Pages', 'simple-post-like' ); ?></label>
              </th>
              <td>
                <label class="spl-toggle">
                  <input type="checkbox" id="inject_archive" name="inject_archive" value="1"
                      <?php checked( $settings['inject_archive'] ); ?>>
                  <span class="spl-toggle__slider"></span>
                  <span class="spl-toggle__label">
                  <?php esc_html_e( 'Show after each post on archive, category, tag, and blog index pages.', 'simple-post-like' ); ?>
                </span>
                </label>
              </td>
            </tr>

          </table>
        </div>

        <!-- Section: Shortcode Reference -->
        <div class="spl-section spl-section--flat">
          <div class="spl-section__head">
            <h2 class="spl-section__title"><?php esc_html_e( 'Shortcode', 'simple-post-like' ); ?></h2>
            <p class="spl-section__desc"><?php esc_html_e( 'Place the like button anywhere manually.', 'simple-post-like' ); ?></p>
          </div>
          <div class="spl-shortcodes">
            <div class="spl-shortcode">
              <code>[simple_post_like]</code>
              <span><?php esc_html_e( 'Current post, default style', 'simple-post-like' ); ?></span>
            </div>
            <div class="spl-shortcode">
              <code>[simple_post_like post_id="123"]</code>
              <span><?php esc_html_e( 'Specific post by ID', 'simple-post-like' ); ?></span>
            </div>
            <div class="spl-shortcode">
              <code>[simple_post_like style="icon_only"]</code>
              <span><?php esc_html_e( 'Override display style', 'simple-post-like' ); ?></span>
            </div>
          </div>
        </div>

        <div class="spl-submit-row">
          <?php
            submit_button(
                esc_html__( 'Save Settings', 'simple-post-like' ),
                'primary spl-button',
                'submit',
                false,
                [ 'class' => 'spl-button' ]
            );
          ?>
        </div>

      </form>
      <?php
    }

    /* ------------------------------------------------------------------ */
    /* Statistics Tab                                                        */
    /* ------------------------------------------------------------------ */

    private function render_statistics_tab(): void {
      $stats       = Stats::instance();
      $total_likes = $stats->get_total_likes();
      $liked_posts = $stats->get_liked_posts_count();
      $top_posts   = $stats->get_most_liked_posts( 10 );
      $top_post    = $top_posts[0] ?? null;
      ?>

      <!-- Summary Cards -->
      <div class="spl-stat-cards">

        <div class="spl-stat-card spl-stat-card--likes">
          <span class="spl-stat-card__icon dashicons dashicons-heart"></span>
          <div class="spl-stat-card__body">
          <span class="spl-stat-card__number">
            <?php echo esc_html( LikeButton::instance()->format_like_count( $total_likes ) ); ?>
          </span>
            <span class="spl-stat-card__label"><?php esc_html_e( 'Total Likes', 'simple-post-like' ); ?></span>
          </div>
        </div>

        <div class="spl-stat-card spl-stat-card--posts">
          <span class="spl-stat-card__icon dashicons dashicons-admin-post"></span>
          <div class="spl-stat-card__body">
            <span class="spl-stat-card__number"><?php echo esc_html( (string) $liked_posts ); ?></span>
            <span class="spl-stat-card__label"><?php esc_html_e( 'Posts with Likes', 'simple-post-like' ); ?></span>
          </div>
        </div>

        <div class="spl-stat-card spl-stat-card--most">
          <span class="spl-stat-card__icon dashicons dashicons-awards"></span>
          <div class="spl-stat-card__body">
          <span class="spl-stat-card__number">
            <?php echo $top_post
                ? esc_html( LikeButton::instance()->format_like_count( $top_post->like_count ) )
                : '—'; ?>
          </span>
            <span class="spl-stat-card__label"><?php esc_html_e( 'Most Liked Post', 'simple-post-like' ); ?></span>
          </div>
        </div>

      </div>

      <!-- Most Liked Posts List -->
      <div class="spl-section spl-most-liked-posts">
        <div class="spl-section__head spl-most-liked-posts__head">
          <h2 class="spl-section__title"><?php esc_html_e( 'Most Liked Posts', 'simple-post-like' ); ?></h2>
          <p class="spl-section__desc"><?php esc_html_e( 'Top 10 posts ranked by like count.', 'simple-post-like' ); ?></p>
        </div>

        <?php if ( empty( $top_posts ) ) : ?>
          <div class="spl-empty">
            <span class="dashicons dashicons-heart spl-empty__icon"></span>
            <p><?php esc_html_e( 'No likes yet. Share your posts to get started!', 'simple-post-like' ); ?></p>
          </div>
        <?php else : ?>
          <ul class="spl-post-list">

            <?php /* Header row */ ?>
            <li class="spl-post-list__header">
              <span class="spl-col-rank"><?php esc_html_e( '#', 'simple-post-like' ); ?></span>
              <span class="spl-col-title"><?php esc_html_e( 'Post Title', 'simple-post-like' ); ?></span>
              <span class="spl-col-type"><?php esc_html_e( 'Type', 'simple-post-like' ); ?></span>
              <span class="spl-col-likes"><?php esc_html_e( 'Likes', 'simple-post-like' ); ?></span>
              <span class="spl-col-actions"><?php esc_html_e( 'Actions', 'simple-post-like' ); ?></span>
            </li>

            <?php foreach ( $top_posts as $index => $post ) :
              $rank_class = match ( $index ) {
                0 => 'spl-rank--gold',
                1 => 'spl-rank--silver',
                2 => 'spl-rank--bronze',
                default => '',
              };
              ?>
              <li class="spl-post-list__item">

          <span class="spl-col-rank">
            <span class="spl-rank <?php echo esc_attr( $rank_class ); ?>">
              <?php echo esc_html( (string) ( $index + 1 ) ); ?>
            </span>
          </span>

                <span class="spl-col-title">
            <strong><?php echo esc_html( $post->post_title ?: __( '(no title)', 'simple-post-like' ) ); ?></strong>
          </span>

                <span class="spl-col-type">
            <span class="spl-pt-badge"><?php echo esc_html( $post->post_type ); ?></span>
          </span>

                <span class="spl-col-likes">
            <span class="spl-like-pill">
              <span class="dashicons dashicons-heart"></span>
              <?php echo esc_html( LikeButton::instance()->format_like_count( $post->like_count ) ); ?>
            </span>
          </span>

                <span class="spl-col-actions">
            <a href="<?php echo esc_url( (string) get_permalink( $post->ID ) ); ?>"
               target="_blank" rel="noopener noreferrer"
               title="<?php esc_attr_e( 'View post', 'simple-post-like' ); ?>">
              <span class="dashicons dashicons-external"></span>
            </a>
            <a href="<?php echo esc_url( (string) get_edit_post_link( $post->ID ) ); ?>"
               title="<?php esc_attr_e( 'Edit post', 'simple-post-like' ); ?>">
              <span class="dashicons dashicons-edit"></span>
            </a>
          </span>

              </li>
            <?php endforeach; ?>

          </ul>
        <?php endif; ?>
      </div>

      <?php
    }

    /* ------------------------------------------------------------------ */
    /* Helpers                                                              */
    /* ------------------------------------------------------------------ */

    /**
     * Get all public post types, excluding attachments.
     *
     * @return array<string, string> Slug => Label.
     */
    private function get_public_post_types(): array {
      $post_types = get_post_types( [ 'public' => true ], 'objects' );
      $result     = [];

      foreach ( $post_types as $pt ) {
        if ( $pt->name === 'attachment' ) {
          continue;
        }
        $result[ $pt->name ] = $pt->label;
      }

      return $result;
    }

    /**
     * Clean up admin page - hide other notices
     */
    public function clean_admin_page(): void {
      $screen = get_current_screen();

      // Check if current page is any onemeta page
      if (!$screen || strpos($screen->id, 'simple-post-like') === false) {
        return;
      }

      // CSS to hide all notices (except onemeta)
      ?>
      <style>
        /* Hide ALL notices on any onemeta page */
        body[id*="simple-post-like"] .notice:not(.spl-notice),
        body[id*="simple-post-like"] .update-nag,
        body[id*="simple-post-like"] .updated {
          display: none !important;
        }

        /* Show only simple-post-like (spl) notices */
        .spl-notice {
          display: block !important;
          margin: 15px 0 !important;
          border-left-color: #2271b1 !important;
        }

        /* Optional: Clean up admin UI */
        body[id*="simple-post-like"] #wpbody-content > .notice {
          display: none;
        }

        /* Hide screen options & help tabs */
        body[id*="simple-post-like"] #screen-meta,
        body[id*="simple-post-like"] #screen-meta-links {
          display: none;
        }
      </style>

      <!-- Optional: Remove admin footer text -->
      <script>
        document.addEventListener('DOMContentLoaded', function() {
          // Remove footer text
          const footer = document.getElementById('footer-left');
          if (footer) {
            footer.innerHTML = '';
          }

          // Remove footer upgrade notice
          const upgrade = document.getElementById('footer-upgrade');
          if (upgrade) {
            upgrade.remove();
          }
        });
      </script>
      <?php

      // Remove admin notices via PHP
      remove_all_actions('admin_notices');
      remove_all_actions('all_admin_notices');
    }
  }