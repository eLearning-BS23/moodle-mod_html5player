define(['jquery'], function ($) {
    const loadBrightCoveJs = (accountId, playerId) => {
        window.require.config({
            'paths': {
                'bc': `https://players.brightcove.net/${accountId}/${playerId}_default/index.min`
            },
            waitSeconds: 30
        });
    }

    // On Load meta data event and listener
    const html5playerOnLoadMetaData = (player) => {
        player.on('loadedmetadata', function(e){
            console.log(e);
            console.log(player.duration());
            const playListsItems = player.playlist();
            playListsItems.forEach( (item, index  ) => {
                console.log(item);
                console.log(index);
            });
        });
    }

    const initBrightCovePlayer = (accountId, playerId) => {

        // Make brightcove js in Require js module as bc.
        loadBrightCoveJs(accountId, playerId);

        require(['bc'], function() {
            console.info(`Brightcove player js loaded...`);
            const myPlayer = videojs.getPlayer(`brightcove-player-${playerId}`);
            html5playerOnLoadMetaData(myPlayer);
            // myPlayer.on('playstart')
        });
    }

    return {
       init: initBrightCovePlayer
   }
});