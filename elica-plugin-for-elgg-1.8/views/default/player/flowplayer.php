<?php
/**
 * Video Player
 *
 * @package Elica
 *
 * @licence GNU Public License version 2
 * @author Atanas Georgiev <atanas@fmi.uni-sofia.bg>
 */

elgg_load_css('elica.flowplayer.css');
elgg_load_css('elica.flowplayer-custom.css');
elgg_load_js('elica.flowplayer');

$data_swf_url = elgg_get_site_url() . 'mod/elica/vendors/flowplayer/flowplayer.swf';
$video_url = $vars['video_url'];
$image_url = $vars['image_url'];
$default_video_url = elgg_get_site_url() . 'mod/elica/vendors/flowplayer/flowplayer-700.mp4';
$default_image_url = elgg_get_site_url() . 'mod/elica/vendors/flowplayer/rendering.jpg';
?>

<script type="text/javascript">
flowplayer.conf = {
   native_fullscreen: true,
   adaptiveRatio: true,
   splash: true
};
flowplayer(function (api, root) {
  api.bind("load", function (e, api) {
  });
  var recoverid = 0;
  api.bind("error", function (e, api, err) {
    if (err.code === 4) { // Video file not found
      // increment recoverid
      recoverid += 1;
      // properly unload player
      api.unload();
      // replace container
      root.replaceWith(
        // here we also change the skin color to alert the user
        '<div id="recover' + recoverid + '" class="recover color-alt2 no-background aside-time">'
      );
      //container with unique id
      var recovercontainer = $("#recover" + recoverid);
      // notification - can be omitted
      recovercontainer.append('<p class="replacement"><?php print elgg_echo('elica:simulation:processing'); ?></p>');
      // install replacement with safe video sources
      recovercontainer.flowplayer({
        playlist: [
          [
            { mp4:   "<?php print $default_video_url; ?>" },
          ]
        ]
      });
      // only load explicitly on devices supporting autoplay
      // on iOS the on-demand install already starts playback
      if (flowplayer.support.seekable) {
        recovercontainer.data("flowplayer").load();
      }
    }
  });
});
</script>
<a name="simulation-run-<?php print $vars['run_guid'];?>"></a>
<div class="flowplayer is-splash aside-time" autoplay="autoplay" id="player<?php echo $vars['run_guid'];?>" data-swf="<?php print $data_swf_url;?>" 
  style="background: #eee url(<?php print $image_url;?>) center no-repeat; background-size: cover;">
   <video>
      <source type="video/mp4" src="<?php print $video_url;?>">
   </video>
</div>
