<?php

// DefaultTwitter Display Template
$twitter_template['tweet_template'] = '<li class="row tweet">
			<div class="col-xs-4 col-sm-4 feed-icon"><a href="{link}" target="_blank" title="View on Twitter">'. $custIcon .'</a></div>
				<div class="col-xs-12 col-sm-12 feed-content">
					<header><a href="{link}" target="_blank" title="View on Twitter"><strong>@'. $a['screenname'] .'</strong></a> - {date}</header>
					<div class="status">{tweet}</div>
				</div>
			</li>';
?>