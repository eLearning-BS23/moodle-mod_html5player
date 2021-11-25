define(['jquery','core/ajax'], function ($, Ajax) {
    const loadBrightCoveJs = (accountId, playerId) => {
        window.require.config({
            'paths': {
                'bc': `https://players.brightcove.net/${accountId}/${playerId}_default/index.min`
            },
            waitSeconds: 30
        });
    }

    const set_course_module_progress = (id, videoid, progress) => {
        let promise;

        promise = Ajax.call([{
            methodname: 'mod_html5player_set_module_progress',
            args: {
                id, // course module id.
                videoid, // Brightcove video id.
                progress, // Progress percentage
            }
        }]);

        promise[0].then(function(results) {
            console.log(results)

        }).fail((e) => {
            console.log(e)
        });
    }

    // On Load meta data event and listener
    const html5playerOnLoadMetaData = (player) => {
        let interval;

        player.on('loadedmetadata', function(e){
            // console.log(player.duration());
            const playListsItems = player.playlist();
            playListsItems.forEach( (item, index  ) => {
                console.log(item);
                console.log(index);
            });
        });


        player.on('playing',(e)=> {
            console.info(`Video playing...`)
            interval = setInterval(function(){
                const currentTime = player.currentTime();
                console.log(`Video playing. Video current progress is : ${currentTime}`)
                set_course_module_progress(85,'6266514986001',currentTime)
            }, 5000);

        })
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