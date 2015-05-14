<?php
/*
Date format
More info
*/
class thc_settings {
	
	const SETTINGS_PAGE_SLUG = 'events';
	const SETTINGS_SECTION_NAME = 'thc_settings_section';
	const SETTINGS_GROUP_NAME = 'thc_option_group';

	/**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {	
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'thc_settings_init' ) );		
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {		
		add_submenu_page(
			'edit.php?post_type=' . thc_constants::POSTTYPE,
			'The Holiday Calendar settings',
			'Settings',
			'manage_options',
			self::SETTINGS_PAGE_SLUG,
			array( $this, 'create_admin_page' )
		);
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'thc_settings' );
        ?>
        <div class="wrap">
            <h2>The Holiday Calendar Settings</h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( self::SETTINGS_GROUP_NAME );   
                do_settings_sections( self::SETTINGS_PAGE_SLUG );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function thc_settings_init()
    {        
        register_setting(
            self::SETTINGS_GROUP_NAME, // Option group
            'thc_settings', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            self::SETTINGS_SECTION_NAME, // ID
            null, // Title
            array( $this, 'print_section_info' ), // Callback
            self::SETTINGS_PAGE_SLUG // Page
        );  

        add_settings_field(
            'thc_date_format', // ID
            'Date format', // Title 
            array( $this, 'date_format_render' ), // Callback
            self::SETTINGS_PAGE_SLUG, // Page
            self::SETTINGS_SECTION_NAME // Section           
        );      

        add_settings_field(
            'thc_hide_read_more', 
            'Hide read more link', 
            array( $this, 'read_more_render' ), 
            self::SETTINGS_PAGE_SLUG, 
            self::SETTINGS_SECTION_NAME
        );      
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['thc_date_format'] ) )
            $new_input['thc_date_format'] = sanitize_text_field( $input['thc_date_format'] );

        if( isset( $input['thc_hide_read_more'] ) )
            $new_input['thc_hide_read_more'] = sanitize_text_field( $input['thc_hide_read_more'] );

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        //print 'The following settings apply to all holiday calendars:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function date_format_render()
    {	
		$dateFormats = array('dd-mm-yy' => '0', 'dd.mm.yy' => '1', 'dd.mm.yyyy' => '2', 'dd/mm/yy' => '3', 'dd/mm/yyyy' => '4', 'mm/dd/yyyy' => '5', 'yy/mm/dd' => '6', 'yyyy? m? d?' => '7');
		
        ?>
			<select name='thc_settings[thc_date_format]'>
				<?php foreach($dateFormats as $dateFormat => $code) { ?>
				  <option <?php selected( $this->options['thc_date_format'], $code ); ?> value="<?php echo $code; ?>"><?php echo $dateFormat; ?></option>
				<?php } ?>
			</select>
		<?php
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function read_more_render()
    {
        ?>
			<input type='checkbox' name='thc_settings[thc_hide_read_more]' <?php checked( $this->options['thc_hide_read_more'], 1 ); ?> value='1'>
		<?php
    }
}