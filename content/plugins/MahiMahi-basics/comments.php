<?php

function mahibasics_comments_clauses($clauses, $wp_comment_query) {
	global $wpdb;

	// deprecated where_
	foreach($wp_comment_query->query_vars as $k => $v):
		if ( preg_match("#^where_(.*)$#", $k, $tmp)):
			unset($wp_comment_query->query_vars[$k]);
			$wp_comment_query->query_vars[$tmp[1]] = $k;
		endif;
	endforeach;

	$joined = array();

	if ( isset($wp_comment_query->query_vars['orderby_usermeta']) ) :
		if ( $wp_comment_query->query_vars['orderby_usermeta'] ) :
			$key = preg_replace("#[^\w\d]*#",'',$wp_comment_query->query_vars['orderby_usermeta']);
			if (!in_array($key,$joined)):
				$joined[] = $key;
				$clauses['join']	.=	"	LEFT JOIN {$wpdb->usermeta} UM_".$key." ON wp_comments.user_id = UM_".$key.".user_id ";
				$clauses['where']	.=	"	AND UM_".$key.".meta_key = '".$wp_comment_query->query_vars['orderby_usermeta']."' ";
			endif;
			$clauses['orderby']	=	" UM_".$key.".meta_value ".$wp_comment_query->query_vars['order'].", comment_date ";
		endif;
	endif;

	if ( isset($wp_comment_query->query_vars['orderby_meta']) ) :
		if ( $wp_comment_query->query_vars['orderby_meta'] ) :
			$key = preg_replace("#[^\w\d]*#",'',$wp_comment_query->query_vars['orderby_meta']);
			if (!in_array($key,$joined)):
				$joined[] = $key;
				$clauses['join']	.=	"	LEFT JOIN {$wpdb->commentmeta} CM_".$key." ON wp_comments.comment_ID = CM_".$key.".comment_id ";
				$clauses['where']	.=	"	AND CM_".$key.".meta_key = '".$wp_comment_query->query_vars['orderby_meta']."' ";
			endif;
			$clauses['orderby']	=	" CM_".$key.".meta_value ".$wp_comment_query->query_vars['order'].", comment_date ";
		endif;
	endif;

	if ( isset($wp_comment_query->query_vars['usermeta_key']) ) :
		$key = preg_replace("#[^\w\d]*#",'',$wp_comment_query->query_vars['usermeta_key']);
		if ( $wp_comment_query->query_vars['usermeta_key'] ) :
			if (isset($wp_comment_query->query_vars['usermeta_compare']))
				$compare = $wp_comment_query->query_vars['usermeta_compare'];
			else $compare = "=";
			if ( ($wp_comment_query->query_vars['usermeta_type'] == 'numeric') && !is_numeric($wp_comment_query->query_vars['usermeta_value']) )
				break;
			$value = $wp_comment_query->query_vars['usermeta_value'];
			if ( $wp_comment_query->query_vars['usermeta_type'] != 'numeric')
				$value = "'".$value."'";

			if (!in_array($key,$joined)):
				$joined[] = $key;
				$clauses['join']	.=	"	LEFT JOIN {$wpdb->usermeta} UM_".$key." ON wp_comments.user_id = UM_".$key.".user_id ";
				$clauses['where']	.=	"	AND UM_".$key.".meta_key = '".$wp_comment_query->query_vars['usermeta_key']."' ";
			endif;
			$clauses['where']	.=	"	AND UM_".$key.".meta_value+0 ".$compare." ".$value." ";
		endif;
	endif;

	if ( isset($wp_comment_query->query_vars['meta_key']) ) :
		$key = preg_replace("#[^\w\d]#",'',$wp_comment_query->query_vars['meta_key']);
		if ( $wp_comment_query->query_vars['meta_key'] ) :
			if (isset($wp_comment_query->query_vars['meta_compare']))
				$compare = $wp_comment_query->query_vars['meta_compare'];
			else $compare = "=";
			if ( ($wp_comment_query->query_vars['meta_type'] == 'numeric') && !is_numeric($wp_comment_query->query_vars['meta_value']) )
				break;
			$value = $wp_comment_query->query_vars['meta_value'];
			if ( $wp_comment_query->query_vars['meta_type'] != 'numeric')
				$value = "'".$value."'";
			else
				$prefix = '+0';

			if (!in_array($key,$joined)):
				logr(get_caller());
				$joined[] = $key;
				$clauses['join']	.=	"	LEFT JOIN {$wpdb->commentmeta} CM_".$key." ON wp_comments.comment_ID = CM_".$key.".comment_id ";
				$clauses['where']	.=	"	AND CM_".$key.".meta_key = '".$wp_comment_query->query_vars['meta_key']."' ";
			endif;
			$clauses['where']	.=	"	AND CM_".$key.".meta_value".$prefix." ".$compare." ".$value." ";
		endif;
	endif;

	if ( isset($wp_comment_query->query_vars['debug']) ) :
		if ( $wp_comment_query->query_vars['debug'] == 'xmpr' ) :
			xmpr($wp_comment_query->query_vars);
			xmpr($clauses);
		else:
			logr($wp_comment_query->query_vars);
			logr($clauses);
		endif;
	endif;


	return $clauses;
}

add_filter('comments_clauses', 'mahibasics_comments_clauses', 99, 2);


