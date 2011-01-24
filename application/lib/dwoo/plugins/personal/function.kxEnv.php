<?php
/* ------ Block plugin ------ */

/**
 * Dwoo block function, provides a way for dwoo to get using kxEnv.
 *
 * The block content is what is needed to get.
 *
 */
 
function Dwoo_Plugin_kxEnv(Dwoo $dwoo, $var) {
	return kxEnv::Get('kx:' . $var);
} 

?>