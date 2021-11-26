define(['jquery','core/ajax'], function ($, Ajax) {

    let interval;

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
        player.on('loadedmetadata', function(e){
            // console.log(player.duration());
            const playListsItems = player.playlist();
            playListsItems.forEach( (item, index  ) => {
                console.log(item);
                console.log(index);
            });
        });
    }


    const html5playerOnPlay = (player,course, cm, video_id) => {
        player.on('play',(e)=> {
            console.info(`Video started playing...`)
            interval = setInterval(function(){
                const currentTime = player.currentTime();
                console.log(`Video playing. Video current progress is : ${currentTime}`)
                set_course_module_progress(cm,video_id,currentTime)
            }, 5000);

        })

        player.on('pause',(e)=>{
            clearInterval(interval);
        })

        player.on('ended',(e)=>{
            const currentTime = player.currentTime();
            console.log(`Video ended...`)
            set_course_module_progress(cm,video_id,currentTime)
            clearInterval(interval);
        })

        // player.on('stopped')
    }

    const initBrightCovePlayer = (course, cm, accountId, playerId, video_id) => {
        // Make brightcove js in Require js module as bc.
        loadBrightCoveJs(accountId, playerId);

        require(['bc'], function() {
            console.info(`Brightcove player js loaded...`);
            const myPlayer = videojs.getPlayer(`brightcove-player-${playerId}`);
            // Do meta loaded stuffs here.
            console.info('Player meta data loaded...')
            html5playerOnLoadMetaData(myPlayer);

            // Do Start playing stuffs here.
            html5playerOnPlay(myPlayer,course, cm, video_id)
        });
    }

    return {
       init: initBrightCovePlayer
   }
});