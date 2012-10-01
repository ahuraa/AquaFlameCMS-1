<?php
if(!isset($_SESSION['username'])){
    ?>
    <div class="user-plate">
        <a href="?login" onclick="return Login.open()" class="card-login">
        <? echo $uplate['login']?>
        </a>
        
        <div class="card-overlay"></div>
    </div>
    <?php 
}else{
    $side = rand(1,2);
    switch($side){
        case 1:
          $side = "alliance";
        break;
        case 2:
          $side = "horde";
        break;
    }
    
    if(isset($_GET['cc'])){
        $character_id = intval($_GET['cc']);
        $realm_id = intval($_GET['r']);
        
        $realm = mysql_fetch_assoc(mysql_query("SELECT * FROM $server_adb.realmlist WHERE id = '".$realm_id."'"));
        $realm_extra = mysql_fetch_assoc(mysql_query("SELECT * FROM realms WHERE realmid = '".$realm_id."'"));
        
        $server_cdb = $realm_extra['char_db'];
        
        $select = mysql_fetch_assoc(mysql_query("SELECT guid,race,gender FROM $server_cdb.characters WHERE guid = '".$character_id."' AND account = '".$account_information['id']."'"));
        
        if($select){
            $avatar = $select['race']."-".$select['gender'].".jpg";	
            $update = mysql_query("UPDATE users SET `avatar` = '".$avatar."', `character` ='".$character_id."', `char_realm` = '".$realm_extra['id']."' WHERE id = '".$account_extra['id']."'");
            echo '<meta http-equiv="refresh" content="0;url=index.php"/>';
        }else{
            echo '<meta http-equiv="refresh" content="0;url=index.php"/>';
        }
    }
      
    $login_query = mysql_query("SELECT * FROM $server_adb.account WHERE username = '".mysql_real_escape_string($_SESSION["username"])."'");
    $login2 = mysql_fetch_assoc($login_query);	
    
	$uI = mysql_query("SELECT * FROM $server_db.users WHERE id = '".$login2['id']."'");
	@$userInfo = mysql_fetch_assoc($uI);
    
    $numchars = 0;
    
    if($account_extra['character'] != 0){
        $realm_extra = mysql_fetch_assoc(mysql_query("SELECT * FROM realms WHERE realmid = '".$account_extra['char_realm']."'"));
        $realm = mysql_fetch_assoc(mysql_query("SELECT * FROM $server_adb.realmlist WHERE id = '".$realm_extra['realmid']."'"));
        
        $server_cdb = $realm_extra['char_db'];
        $server_wdb = $realm_extra['world_db'];
        
        $query001 = mysql_query("SELECT * FROM $server_cdb.characters WHERE account = '".$account_information['id']."' AND guid = '".$account_extra['character']."'");
        if(mysql_num_rows($query001) > 0){
            $actualchar = mysql_fetch_assoc($query001);
            $numchars++;
        }
		else {
            mysql_query("UPDATE $server_db.users SET `character` = 0 WHERE id = $account_extra[id]")
				or die(mysql_error("Cannot remove character from web db"));
            header("Location : index.php");
        }
        
    }else{
        
        $get_realms = mysql_query("SELECT * FROM $server_adb.realmlist ORDER BY `id` DESC");
        if($get_realms){
            while($realm = mysql_fetch_array($get_realms)){
                $realm_extra = mysql_fetch_assoc(mysql_query("SELECT * FROM realms WHERE realmid = '".$realm['id']."'"));
                
                $server_cdb = $realm_extra['char_db'];
                $server_wdb = $realm_extra['world_db'];
                                   
                $check_chars = mysql_query("SELECT * FROM $server_cdb.characters WHERE account = '".$account_information['id']."' ORDER BY `guid` DESC");
                if($check_chars){
                    
                    //Re-Check
                    $account_extra = mysql_fetch_assoc(mysql_query("SELECT * FROM $server_db.users WHERE id = '".$account_information['id']."'"));
                    
                    if($account_extra['character'] == 0){
                        $actualchar = mysql_fetch_assoc($check_chars);
                        $avatar = $actualchar['race']."-".$actualchar['gender'].".jpg";
                        
                        @$set_character = mysql_query("UPDATE users SET `avatar` = '".$avatar."', `character` = '".$actualchar['id']."', `char_realm` = '".$realm_extra['id']."' WHERE id = '".$account_extra['id']."'");
                   }
                }
                $numchars = $numchars + mysql_num_rows($check_chars); 
            }
        }
                
    }
    
    if($numchars != 0){
    
	switch( $actualchar["race"] )
	{
		case 2:
		case 5:
		case 6:
		case 8:
        case 9:
		case 10: $side = "horde";
		break;
		default: $side = "alliance";
	}
    
    function racetxt($race){
        switch($race){
            case 1: return "Human"; break;
            case 2: return "Orc"; break;
            case 3: return "Dwarf"; break;
            case 4: return "Night Elf"; break;
            case 5: return "Undead"; break;
            case 6: return "Tauren"; break;
            case 7: return "Gnome"; break;
            case 8: return "Troll"; break;
            case 9: return "Goblin"; break;
            case 10: return "Blood Elf"; break;
            case 11: return "Draenei"; break;
            case 22: return "Worgen"; break;
            
        }
    }
            
    function classtxt($class){
        switch($class){
            case 1: return "Warrior"; break;
            case 2: return "Paladin"; break;
            case 3: return "Hunter"; break;
            case 4: return "Rogue"; break;
            case 5: return "Priest"; break;
            case 6: return "Death Knight"; break;
            case 7: return "Shaman"; break;
            case 8: return "Mage"; break;
            case 9: return "Warlock"; break;
            case 10: return "Druid"; break;                
        }
    }
    
	?>
<div class="user-plate">
<div id="user-plate" class="card-character plate-<?php echo $side; ?> ajax-update" style="background: url(<?php echo $website['root']; ?>wow/static/images/2d/card/<?php echo $actualchar["race"] . "-" . $actualchar["gender"];?>.jpg) 0 100% no-repeat;">
<div class="card-overlay"></div>
<span class="hover"></span>
</a>
<div class="meta">
<div class="player-name"><?php echo $account_extra['firstName'].' '.$account_extra['lastName']; ?></div>
	  
	  <div class="character">
	  <a class="character-name context-link" href="#" rel="np" data-tooltip="Change character"><?php echo $actualchar["name"]; ?><span class="arrow"></span></a>
	  <div class="guild">
<a class="guild-name" href="#">
<?php echo $realm['name'] ?>
</a>
</div>
		<div id="context-1" class="ui-context character-select">
		
		  <div class="context">
			<a href="javascript:;" class="close" onclick="return CharSelect.close(this);"></a>
			
			<div class="context-user">
			<strong><?php echo $actualchar["name"]; ?></strong>
			<br />
			<span class="realm up"><?php echo $realm['name'] ?></span>
			</div>
		  
			<div class="context-links">
			<a href="<?php echo $website['root'];?>advanced.php?name=<?php echo $actualchar["name"]; ?>" title="<? echo $uplate['profile']; ?>" class="icon-profile link-first"><? echo $uplate['profile']; ?></a>
			<a href="#" title="<?php echo $uplate['post']; ?>" class="icon-posts"> </a>
			<a href="#" title="<?php echo $uplate['auction']; ?>" rel="np"class="icon-auctions"> </a>
			<a href="#" title="<?php echo $uplate['events']; ?>" rel="np" class="icon-events link-last"> </a>
			</div>
		  </div>
          
		  <div class="character-list">
			<div class="primary chars-pane">
                <div class="char-wrapper">
                    <?php
                   	
                    
                    $get_realms = mysql_query("SELECT * FROM $server_adb.realmlist ORDER BY `id` DESC");
                    if($get_realms){
                        while($realm = mysql_fetch_array($get_realms)){
                            $realm_extra = mysql_fetch_assoc(mysql_query("SELECT * FROM realms WHERE realmid = '".$realm['id']."'"));
                            
                            $server_cdb = $realm_extra['char_db'];                                               
                            $check_chars = mysql_query("SELECT * FROM $server_cdb.characters WHERE account = '".$account_information['id']."' ORDER BY `guid` DESC");
                            
                            $current_realm = mysql_fetch_assoc(mysql_query("SELECT * FROM realms WHERE id = '".$account_extra['char_realm']."'"));
                            
                            if($check_chars){
                                while($char = mysql_fetch_array($check_chars)){
                                    
                                    echo '
                                    <a href="?cc='.$char['guid'].'&r='.$realm['id'].'" class="char ';
                                    if($char['guid'] == $actualchar['guid'] && $realm['id'] == $current_realm['realmid']) echo 'pinned';
                                    echo '" rel="np">
                                        <span class="pin"></span>
                                        <span class="name">'.$char["name"].'</span>
                                        <span class="class color-c'.$char["class"].'">'.$char["level"] . ' ' . racetxt($char['race']) . ' ' . classtxt($char['class']).'</span>
                                        <span class="realm';
                                        
                                        $host = $realm['address'];
                                        $world_port = $realm['port'];
                                        $world = @fsockopen($host, $world_port, $err, $errstr, 2);
                                        
                                        if(!$world) echo ' down';
                                        
                                        echo '">'.$realm['name'].'</span>
                                    </a>
                                    ';
                                }
                            }
                        }
                    }
                    ?>
                </div>
                
                <a href="#" class="manage-chars" onclick=""><!--CharSelect.swipe('in', this); return false;-->
                    <span class="plus"></span>
                    <?php echo $uplate['manage']; ?><br />
                    <span><?php echo $uplate['customize']; ?></span>
                </a>
			</div>
			<!--
			<div class="secondary chars-pane" style="display: none">
			<div class="char-wrapper scrollbar-wrapper" id="scroll">
			<div class="scrollbar">
			<div class="track"><div class="thumb"></div></div>
			</div>
			<div class="viewport">
			<div class="overview">
			<a href="javascript:;"
			class="color-c1 pinned"
			rel="np"
			onmouseover="Tooltip.show(this, $(this).children('.hide').text());">
			<img src="/wow/static/images/icons/race/2-0.gif" alt="" />
			<img src="/wow/static/images/icons/class/1.gif" alt="" />
			28 Aghman
			<span class="hide">Orc Warrior (Burning Steppes)</span>
			</a>
			<a href="/wow/en/character/burning-steppes/stefyvolt/"
			class="color-c2"
			rel="np"
			onclick="CharSelect.pin(1, this); return false;"
			onmouseover="Tooltip.show(this, $(this).children('.hide').text());">
			
			<img src="/wow/static/images/icons/race/10-0.gif" alt="" />
			<img src="/wow/static/images/icons/class/2.gif" alt="" />
			80 Stefyvolt
			<span class="hide">Blood Elf Paladin (Burning Steppes)</span>
			</a>
			<a href="/wow/en/character/burning-steppes/taylda/"
			class="color-c6"
			rel="np"
			onclick="CharSelect.pin(2, this); return false;"
			onmouseover="Tooltip.show(this, $(this).children('.hide').text());">
			<img src="/wow/static/images/icons/race/10-1.gif" alt="" />
			<img src="/wow/static/images/icons/class/6.gif" alt="" />
			62 Taylda
			<span class="hide">Blood Elf Death Knight (Burning Steppes)</span>
			</a>
			<a href="/wow/en/character/burning-steppes/stefybank/"
			class="color-c1"
			rel="np"
			onclick="CharSelect.pin(3, this); return false;"
			onmouseover="Tooltip.show(this, $(this).children('.hide').text());">
			<img src="/wow/static/images/icons/race/6-0.gif" alt="" />
			<img src="/wow/static/images/icons/class/1.gif" alt="" />
			5 Stefybank
			
			<span class="hide">Tauren Warrior (Burning Steppes)</span>
			</a>
			<a href="/wow/en/character/arathi/pvpsausage/"
			class="color-c7"
			rel="np"
			onclick="CharSelect.pin(4, this); return false;"
			onmouseover="Tooltip.show(this, $(this).children('.hide').text());">
			<img src="/wow/static/images/icons/race/2-0.gif" alt="" />
			<img src="/wow/static/images/icons/class/7.gif" alt="" />
			1 Pvpsausage
			<span class="hide">Orc Shaman (Arathi)</span>
			</a>
			<a href="/wow/en/character/ragnaros/adenor/"
			class="color-c6"
			rel="np"
			onclick="CharSelect.pin(5, this); return false;"
			onmouseover="Tooltip.show(this, $(this).children('.hide').text());">
			<img src="/wow/static/images/icons/race/11-0.gif" alt="" />
			<img src="/wow/static/images/icons/class/6.gif" alt="" />
			61 Adenor
			<span class="hide">Draenei Death Knight (Ragnaros)</span>
			</a>
			
			<div class="no-results hide">No characters were found</div>
			</div>
			</div>
			</div>
			<div class="filter">
			<input type="input" class="input character-filter" value="Filter�" alt="Filter�" /><br />
			<a href="javascript:;" onclick="CharSelect.swipe('out', this); return false;">Return to characters</a>
			</div>
			</div>
			-->
		  </div>
		</div>
	  </div>
	</div>
	</div>
	<script type="text/javascript">

	//<![CDATA[
	$(document).ready(function() {
	Tooltip.bind('#header .user-meta .character-name', { location: 'topCenter' });
	});
	//]]>
	</script>
	<?php }else{
	echo'
  <div class="user-plate ajax-update">
    <div class="card-nochars">
	   <div class="player-name">
	     '.$userInfo['firstName'].' '.$userInfo['lastName'].'
	   </div>
     '.$uplate['nochars'].'
    </div>
  </div>';	
	} }
	mysql_select_db($server_db,$connection_setup)or die(mysql_error());?>
