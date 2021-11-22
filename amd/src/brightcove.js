define(['jquery'], function ($) {

    const init = (accountid, playerid) => {
        window.require.config({
            'paths': {
                'bc': `https://players.brightcove.net/${accountid}/${playerid}_default/index.min`
            },
            waitSeconds: 30
        });

        require(['bc'], function() {
            const myPlayer = videojs.getPlayer(`brightcove-player-${playerid}`);
            myPlayer.on('loadedmetadata', function(e){
                console.log(e);
                console.log(myPlayer.duration());
            });

            myPlayer.on('playstart')
        });
    }
    return {
       init: init
   }
});