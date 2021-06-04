<?php

// Version 1.0.1  
// Date June 04 2021
// Ultimate Member phone metakey formatting


add_filter( 'um_view_field_value_text', 'my_custom_view_field_value', 10, 2);

function my_custom_view_field_value( $res, $data ) {
	
	if( $data['metakey'] == 'phone_number' || $data['metakey'] == 'mobile_number' ) {
        if( isset( $res ) && $res != '' ) {
            $mobile = format_phone_string( $res );
            $res = '<a href="tel:' . $mobile[0] . preg_replace( '/\D/', '', $mobile[1] ) . '">' . $mobile[0] . ' ' . $mobile[1] . '</a> ' . $mobile[2];
        }
	}
	return $res;
}

function format_phone_string( $raw_number ) {

    //  Return the phone number in parentheses format, e.g. (123) 456-7890. 
    //  Handles 10 digit numbers with or without country codes and extensions
    //  Source 2nd part from: https://stackoverflow.com/questions/4708248/formatting-phone-numbers-in-php

    $mobile = preg_replace( '/\D/', '', $raw_number );  // remove everything but numbers 
    $str = strlen( $mobile );
    if( $str > 10 ) {    
        $extension = str_replace( array( 'extension', 'ext'), 'x' , $raw_number );
        if( str_contains( $extension, 'x' )) {
            $extension = explode( 'x', $extension );
            $mobile = preg_replace( '/\D/', '', $extension[0] );
            $extension = 'ext' . $extension[1];
        } else $extension = '';

        $str = strlen( $mobile );
        if( $str > 10 && substr( $raw_number, 0, 1 ) == '+' ) {
            $country_code = '+' . substr( $mobile, 0, $str - 10 );
            $mobile = substr( $mobile, -10 );
        } else $country_code = '';
    }

    $arr_number = str_split( $mobile );                     // split each number into an array    
    array_unshift( $arr_number, 'dummy' );                  // add a dummy value to the beginning of the array   
    unset( $arr_number[0] );                                // remove the dummy value so now the array keys start at 1    
    $num_number = count( $arr_number );                     // get the number of numbers in the number    
    $phone_number = '';
    
    if( $num_number > 0 ) {
        for ( $x = $num_number; $x >= 0; $x-- ) {           // loop through each number backward starting at the end
            if ( $x === $num_number - 4 ) {                 // before the fourth to last number
                $phone_number = "-" . $phone_number;
            }
            else if ( $x === $num_number - 7 && $num_number > 7 ) { // before the seventh to last number and only if the number is more than 7 digits
                $phone_number = ") " . $phone_number;
            }
            else if ( $x === $num_number - 10 ) {            // before the tenth to last number
                $phone_number = "(" . $phone_number;
            }        
            $phone_number = $arr_number[$x] . $phone_number; // concatenate each number (possibly with modifications) back on
        }
    }
    return array( $country_code, $phone_number, $extension );
}

add_action( 'um_custom_field_validation_mobile_number', 'um_custom_validate_mobile_number', 30, 3 );
add_action( 'um_custom_field_validation_phone_number',  'um_custom_validate_mobile_number', 30, 3 );

/**
 * Validate field Mobile Number or Phone Number
 * @param string $key
 * @param attay  $array
 * @param array  $args
 */
function um_custom_validate_mobile_number( $key, $array, $args ) {

    //  A regex for a 7 or 10 digit number, with extensions and international prefix allowed
    //  number delimiters are spaces, dashes or periods
    //  Source: https://stackoverflow.com/questions/123559/how-to-validate-phone-numbers-using-regex

    if ( isset( $args[$key] ) && $args[$key] != '' ) {
        if( !preg_match( "/^(?:(?:\+?1\s*(?:[.-]\s*)?)?(?:\(\s*([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9])\s*\)|([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\s*(?:[.-]\s*)?)?([2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?([0-9]{4})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$/i", $args[$key] )) { 
            UM()->form()->add_error( $key, __( 'Please enter valid Mobile/Phone Number.', 'ultimate-member' ));
        }
    }
}
