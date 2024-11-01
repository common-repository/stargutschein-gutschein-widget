<?php
/**  
* Plugin Name: Gutschein Widget
* Plugin URI: http://www.stargutschein.de/
* Description: Zeigt aktuelle Gutscheine von Stargutschein.de als Widget in der Sidebar.
* Version: 1.0
* Author: Andreas Ostermann
* Author URI: http://www.stargutschein.de
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*  Installation:
*   - extract the archive, and upload the plugin directory to your wp-content/plugins/ folder
*   - activate "Stargutschein Widget" in your wordpress admin panel
*   - drag and drop the widget

* Changelog:
*       2014-07-01: 1.0
*           Initial release
*/

/**
 * load widget
 */
add_action( 'widgets_init', 'load_sgs' );

/**
 * register widget
 */
function load_sgs() {
	register_widget( 'StargutscheinWidget' );
}

/**
 * Widget class
*/
class StargutscheinWidget extends WP_Widget {

	function StargutscheinWidget() {

		/* Widget settings. */
		$widget_ops = array( 'classname' => 'stargutschein-widget', 'description' => __('Zeigt die neuesten Schn&auml;ppchen von StarGutschein.de', 'sgs-widget') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 400, 'height' => 350, 'id_base' => 'stargutschein-gutscheine' );

		/* Create the widget. */
		$this->WP_Widget( 'stargutschein-gutscheine', __('Neue Gutscheine', 'sgs-widget'), $widget_ops, $control_ops );
	}

	
	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$name = $instance['name'];
		$showVouchers = $instance['showVouchers'];
		$showFooter = isset( $instance['showFooter'] ) ? $instance['showFooter'] : false;
		$myPosts = array();	
		$linkTitle = $instance['linkTitle'];
		$i = 0;		

		if ($linkTitle) {
			$link1 = "<a rel='nofollow' target='_blank' href='http://www.stargutschein.de'>";
			$link2 = "</a>";
		}
		else {
			$link1 = "";
			$link2 = "";
		}

		/* Before widget (defined by themes). */
		echo $before_widget;
		
		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title )
			echo $before_title . "<img src='http://www.stargutschein.de/theme/img/favicon.png' />&nbsp; $link1". $title . "$link2" . $after_title;

		$xml = file_get_contents('http://www.stargutschein.de/rss.xml');

			
		$xmlobj = new SimpleXMLElement($xml);
				
		echo "<ul>";		
		foreach ($xmlobj->channel->item as $item) {	
		
			if ($i < $showVouchers) {
				echo "<li><a rel='nofollow' target='_blank' href='".$item->link."'>".$item->title."</a></li>";
                   $i++;  
               }
		}
		echo "</ul>";
		 
		if ( $showFooter )
			echo '<p style="font-size:xx-small; text-align:right; line-height:10px"><a rel="nofollow" href="http://www.stargutschein.de" target="_blank" title="Gutscheine online">StarGutschein.de<br/>Gutscheinportal</a></p>';

		/* After widget (defined by themes). */
		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['linkTitle'] = strip_tags( $new_instance['linkTitle'] );

		/* No need to strip tags for showVouchers and showFooter. */
		$instance['showVouchers'] = $new_instance['showVouchers'];
		$instance['showFooter'] = $new_instance['showFooter'];

		return $instance;
	}

	/**
	 * Display the widget settings controls on the widget panel.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => __('Aktuelle Gutscheine', 'sgs-widget'), 'name' => __('John Doe', 'sgs-widget'), 'showVouchers' => '5', 'linkTitle' => true, 'showFooter' => true);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?> 

		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('&Uuml;berschrift:', 'hybrid'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>

		<!-- Link the title to our Website? Checkbox-->
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['linkTitle'], on ); ?> id="<?php echo $this->get_field_id( 'linkTitle' ); ?>" name="<?php echo $this->get_field_name( 'linkTitle' ); ?>" /> 
			<label for="<?php echo $this->get_field_id( 'linkTitle' ); ?>"><?php _e('&Uuml;berschrift als Link?', 'sgs-widget'); ?></label>
		</p>


		<!-- How many Vouchers should be shown?: Select Box -->
		<p>
			<label for="<?php echo $this->get_field_id( 'showVouchers' ); ?>"><?php _e('Wieviele Gutscheine sollen angezeigt werden?', 'sgs-widget'); ?></label> 
			<select id="<?php echo $this->get_field_id( 'showVouchers' ); ?>" name="<?php echo $this->get_field_name( 'showVouchers' ); ?>">
				<option <?php if ( '1' == $instance['showVouchers'] ) echo 'selected="selected"'; ?>>1</option>
				<option <?php if ( '2' == $instance['showVouchers'] ) echo 'selected="selected"'; ?>>2</option>
				<option <?php if ( '3' == $instance['showVouchers'] ) echo 'selected="selected"'; ?>>3</option>
				<option <?php if ( '4' == $instance['showVouchers'] ) echo 'selected="selected"'; ?>>4</option>
				<option <?php if ( '5' == $instance['showVouchers'] ) echo 'selected="selected"'; ?>>5</option>
				<option <?php if ( '6' == $instance['showVouchers'] ) echo 'selected="selected"'; ?>>6</option>
				<option <?php if ( '7' == $instance['showVouchers'] ) echo 'selected="selected"'; ?>>7</option>
				<option <?php if ( '8' == $instance['showVouchers'] ) echo 'selected="selected"'; ?>>8</option>
				<option <?php if ( '9' == $instance['showVouchers'] ) echo 'selected="selected"'; ?>>9</option>
				<option <?php if ( '10' == $instance['showVouchers'] ) echo 'selected="selected"'; ?>>10</option>
			</select>
		</p>

		<!-- Show Footer Checkbox -->
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['showFooter'], on ); ?> id="<?php echo $this->get_field_id( 'showFooter' ); ?>" name="<?php echo $this->get_field_name( 'showFooter' ); ?>" /> 
			<label for="<?php echo $this->get_field_id( 'showFooter' ); ?>"><?php _e('Widget Footer anzeigen?', 'sgs-widget'); ?></label>
		</p>

	<?php
	}
}

?>