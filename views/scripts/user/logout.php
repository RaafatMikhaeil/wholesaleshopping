<!-- BREAD CRUMB NAVIGATOR PANEL -->
<div class="bread_crumb_module">
	<div class="bread_crumb">	
		<ul>
			<li><a href="/dashboard">Home</a></li>
			<li><?php echo $this->title; ?></li>
		</ul>
	</div><!-- end bread_crumb div -->
</div><!-- end bread_crumb_module div -->
<div class="clear"></div>

<!-- SYSTEM MESSAGES -->
<?php if( is_array($this->messages) && count($this->messages) > 0 ) { ?>
	<div id="messages">
		<ul>
		<?php foreach( $this->messages as $message ) { ?>
			<li><?php echo $message; ?></li>
		<?php } ?>
		</ul>
	</div><!-- end messages div -->
<?php } ?>

<div class="logout">
    [ <a href="/login" title="click here to log back into Atlas">Log back into Atlas</a> ]
</div>