<?php 


function buddypress_get_all_members($request){
	$params = $request->get_params();
	$users  = get_users($params);
	$members = [];
	if ( empty($users) ) {
		return new WP_Error( 'no_members', __( 'Can\'t find any members', 'wra_bp' ), array( 'status' => 404 ) );
	} 

	foreach ($users as $user) {

		$members[] = get_member($user->ID);

	}
	return new WP_REST_Response($members, 200 );	
}

function get_member($id) {

	$user = get_userdata($id);

	if(empty($user)) {
		return new WP_Error( 'no_member', sprintf( __( 'Member with ID: %d does not exist.', 'wra_bp' ), $this->id ), array( 'status' => 404 ) );
	}

	$data = [];
	$data['username']     = $user->user_login;
	$data['name']         = $user->display_name;
	$data['first_name']  = $user->first_name;
	$data['last_name']	= $user->last_name;
	$data['email']	    = $user->user_email;
	$data['url']    	= $user->user_url;		
	$data['registered']  = $user->user_registered;
	$data['slug']		= $user->user_nicename;
	$data['avatar']       = array(
		"thumb" => get_member_avatar($id,"thumb"),
		"full"  => get_member_avatar("full")
		);

	if(bp_is_active( 'xprofile' )) {
		$data['xprofile'] = get_member_profile_fields($id);
	}

	return $data;

}

function get_member_avatar($id,$type, $no_grav = true) {

	$args = array(
		"item_id" => $id,
		"type"	  => $type,
		"html"	  => false,
		"no_grav" => $no_grav
		);

	$avatar	= bp_core_fetch_avatar ($args);
	return $avatar;

}

function get_member_profile_fields($id) {

	$fields = array();

	if ( bp_has_profile( array('user_id' => $id) ) ) :

		while ( bp_profile_groups() ) : bp_the_profile_group();

	if ( bp_profile_group_has_fields() ) :

		$group 			= strtolower(bp_get_the_profile_group_name());
	$fields[$group] = array();

	while ( bp_profile_fields() ) : bp_the_profile_field();


	if ( bp_field_has_data() ) : 

		$key    = strtolower(bp_get_the_profile_field_name());
	$value  = strip_tags(bp_get_the_profile_field_value());

	$fields[$group] = array($key => trim(preg_replace('/\s\s+/', ' ', $value)));

	endif; 


	endwhile;

	endif;

	endwhile;

	endif;

	return $fields;

}

