<?php 

function buddypress_get_all_members($request){

	$params = $request->get_params();

	if(isset($params['search'])) {
		$params['search'] = '*' . $params['search'] . '*';
	}

	$users  = get_users($params);
	$return= sizeof($users);

	// For counting total users
	$params2 = $params;
	$params2['number'] = -1;
	$users2  = get_users($params2);
	
	$members = [];
	if ( empty($users) ) {
		header('X-WP-Total: '.count($users2));
		header('X-WP-TotalPages: '.ceil(count($users2)/$params['number']));
		return new WP_REST_Response($users, 200 );	
	} 

	foreach ($users as $user) {

		$members[] = get_member($user->ID);

	}

	$result = count_users();
	$count  = $result['avail_roles']['bbp_participant'];

	header('X-WP-Total: '.count($users2 ));
	header('X-WP-TotalPages: '.ceil($count/$params['number']));

	return new WP_REST_Response($members, 200 );	
}

function get_member($id) {

	$user = get_userdata($id);

	if(empty($user)) {
		return new WP_Error( 'no_member', sprintf( __( 'Member with ID: %d does not exist.', 'wra_bp' ), $this->id ), array( 'status' => 404 ) );
	}

	$data = [];
	$data['ID']     = $user->ID;
	$data['username']     = $user->user_login;
	$data['name']         = $user->display_name;
	$data['first_name']  = $user->first_name;
	$data['last_name']	= $user->last_name;
	$data['email']	    = $user->user_email;
	$data['url']    	= $user->user_url;		
	$data['registered']  = $user->user_registered;
	$data['slug']		= $user->user_nicename;
	$data['state'] = get_user_meta($user->ID,'user_location',true);
	$data['practice_specialty'] = get_user_meta($user->ID,'practice_specialty',true);
	$data['update']=get_usermeta( $user->ID, 'bp_latest_update' )['content'];
	$last_active = bp_get_user_last_activity( $user->ID );
	$datef = __( 'M j, Y @ G:i', 'buddypress' );
	$date  = date_i18n( $datef, strtotime( $last_active ) );

	$data['last_active2'] = $date;
	$data['last_active'] =  bp_core_time_since($last_active); 



	$data['avatar']       = array(
		"thumb" => get_member_avatar($id,"thumb"),
		"full"  => get_member_avatar($id,"full")
		);
	if(bp_is_active( 'xprofile' )) {
		$data['xprofile'] = get_member_profile_fields($id);
	}

	return $data;

}

function buddypress_get_member($request) {

	$id = $request['id'];
	$user = get_userdata($id);

	if(empty($user)) {
		return new WP_Error( 'no_member', sprintf( __( 'Member with ID: %d does not exist.', 'wra_bp' ), $this->id ), array( 'status' => 404 ) );
	}

	$data = [];
	$data['ID']     = $user->ID;
	$data['username']     = $user->user_login;
	$data['name']         = $user->display_name;
	$data['first_name']  = $user->first_name;
	$data['last_name']	= $user->last_name;
	$data['email']	    = $user->user_email;
	$data['url']    	= $user->user_url;		
	$data['registered']  = $user->user_registered;
	$data['slug']		= $user->user_nicename;
	$data['state'] = get_user_meta($user->ID,'user_location',true);
	$data['practice_specialty'] = get_user_meta($user->ID,'practice_specialty',true);
	$data['update']=get_usermeta( $user->ID, 'bp_latest_update' )['content'];
	
	$last_active = bp_get_user_last_activity( $user->ID );
	$datef = __( 'M j, Y @ G:i', 'buddypress' );
	$date  = date_i18n( $datef, strtotime( $last_active ) );
	
	$data['last_active'] =  bp_core_time_since($last_active); 

	$data['last_active2'] = $date;
	$data['avatar']       = array(
		"thumb" => get_member_avatar($id,"thumb"),
		"full"  => get_member_avatar($id,"full")
		);
	if(bp_is_active( 'xprofile' )) {
		$data['xprofile'] = get_member_profile_fields($id);
	}

	return new WP_REST_Response($data, 200 );	

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

