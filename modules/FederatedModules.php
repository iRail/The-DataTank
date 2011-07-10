<?php
/* Copyright (C) 2011 by iRail vzw/asbl
 *
 * Author: Pieter Colpaert <pieter aŧ iRail.be>
 * License: AGPLv3
 *
 * This should become a file which autodetects all modules on other servers.
 * The Federated aspect should care about:
 *    * Interchangability of documentation and stats
 *    * Errorhandling through proxy
 *    * Extra error if server unavailable and deletion when needed
 */


/**
 * An array of all known services
 */
class FederatedModules{
     public static $modules = array(
	  "iRail2" => "http://172.22.32.119/General/"
	  );
}

?>
