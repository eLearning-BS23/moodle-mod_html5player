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
            console.info(`Video completed : ${results.completed}`)

        }).fail((e) => {
            console.log(e)
        });
    }

    const get_course_module_progress = (player, id, videoid) => {
        let promise;

        console.info(`Geting course video progress from store...`)
        promise = Ajax.call([{
            methodname: 'mod_html5player_get_module_progress',
            args: {
                id: +id, // course module id.
                videoid: +videoid, // Brightcove video id.
            }
        }]);

        promise[0].then(function(results) {
            console.info(`Fetched result from store`);
            let progress = results.progress
            if (progress){
                console.info(`Video progress is ${progress}ms`);
                const $currentTime = results.progress / 1000;
                player.currentTime($currentTime)
            }else {
                console.info(`Video progress is null`);
            }

        }).fail((e) => {
            console.log(e)
        });
    }

    // On Load meta data event and listener
    const html5playerOnLoadMetaData = (player, cm, video_id) => {
        player.on('loadedmetadata', function(e){
            get_course_module_progress(player, cm, video_id)
            // console.log(player.duration());
            // const playListsItems = player.playlist();
            // playListsItems.forEach( (item, index  ) => {
            //     console.log(item);
            //     console.log(index);
            // });
        });
    }

    const html5playerOnPlay = (player,html5player) => {

        const course = html5player.course;
        const cm = html5player.cmid;
        let video_id = html5player.video_id;

        player.on('play',(e)=> {
            console.info(`Video started playing...`)
            video_id = player.mediainfo.id;
            interval = setInterval(function(){
                const currentTime = player.currentTime();
                console.log(`Video playing. Video current progress is : ${currentTime}`)
                set_course_module_progress(cm,video_id,currentTime)
            }, +html5player.progress_interval);

        })

        player.on('pause',(e)=>{
            clearInterval(interval);
        })

        player.on('ended',(e)=>{
            const currentTime = player.duration();
            console.log(`Video ended...`)
            set_course_module_progress(cm,video_id,currentTime)
            clearInterval(interval);
        })

        // player.on('stopped')
    }

    // const initBrightCovePlayer = (course, cm, accountId, playerId, video_id) => {
    const initBrightCovePlayer = (html5player) => {
        html5player = JSON.parse(html5player);
        // Make brightcove js in Require js module as bc.
        loadBrightCoveJs(html5player.account_id, html5player.player_id);

        require(['bc'], function(bc) {
            console.info(`Brightcove player js loaded...`);

            // Tracking is enabled for only student.
            if (html5player.is_student){
                const myPlayer = videojs.getPlayer(`brightcove-player-${html5player.player_id}`);
                // Do meta loaded stuffs here.
                console.info('Player meta data loaded...')
                html5playerOnLoadMetaData(myPlayer, html5player.cmid, html5player.video_id);

                // Do Start playing stuffs here.
                html5playerOnPlay(myPlayer,html5player)
            }
        });
    }

    return {
       init: initBrightCovePlayer
   }
});