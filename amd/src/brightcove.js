define(['jquery','core/ajax'], function ($, Ajax) {

    let interval;

    /**
     * Common event listener for brightcove Player.
     * @param player
     * @param html5player
     */
    const html5PlayerGenericPlayerEventListener = (player, html5player) => {
        const cm = html5player.cmid;
        let video_id = html5player.video_id;

        player.on('play',(e)=> {
            console.info(`Video started playing...`)
            video_id = player.mediainfo.id;
            interval = player.setInterval(function(){
                const currentTime = Math.ceil(player.currentTime());
                console.log(`Video playing. Total length: ${player.duration()}. Video current progress is : ${currentTime}`)
                set_course_module_progress(cm,video_id,currentTime)
            }, +html5player.progress_interval);

        })

        player.on('pause',(e)=>{
            player.clearInterval(interval);
        })

        player.on('ended',(e)=>{
            const currentTime = Math.ceil( player.duration());
            console.log(`Video ended...`)
            set_course_module_progress(cm,video_id,currentTime)
            player.clearInterval(interval);
        })
    }

    /**
     * Load brightcove player javascript.
     * @param accountId
     * @param playerId
     */
    const loadBrightCoveJs = (accountId, playerId) => {
        window.require.config({
            'paths': {
                'bc': `https://players.brightcove.net/${accountId}/${playerId}_default/index.min`
            },
            waitSeconds: 30
        });
    }

    /**
     * Set course module video progress
     * @param id
     * @param videoid
     * @param progress
     */
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

    /**
     * Get course module single video progress.
     * @param player
     * @param html5player
     */
    const get_single_video_course_module_progress = (player, html5player) => {
        let promise;

        console.info(`Geting course video progress from store...`)
        promise = Ajax.call([{
            methodname: 'mod_html5player_get_module_progress',
            args: {
                id: html5player.cmid, // course module id.
                videoid: html5player.video_id, // html5videos table PK.
            }
        }]);

        promise[0].then(function(results) {
            console.info(`Fetched result from store`);
            let progress = results.progress
            if (progress){
                const duration = player.duration();
                const currentTime = Math.floor(results.progress) / 1000;
                console.info(`Duration is: ${duration} and Video progress is ${currentTime} seconds`);
                if(duration >= currentTime){
                    player.currentTime(currentTime);
                }else {
                    player.currentTime(duration - 1);
                }
            }else {
                console.info(`Video progress is ${results.progress}`);
            }

        }).fail((e) => {
            console.log(e)
        });
    }


    /**
     * Get course module single video progress.
     * @param player
     * @param html5player
     */
    const get_playlist_video_progress = (player, html5player) => {
        let promise;

        console.info(`Getting course video progresses from store...`)
        promise = Ajax.call([{
            methodname: 'mod_html5player_get_module_progresses',
            args: {
                id: html5player.cmid, // course module id.
            }
        }]);

        promise[0].then(function(results) {
            console.info(`Fetched module progresses result from store`);
            console.log(results);
            // let progress = results.progress
            // if (progress){
            //     const duration = player.duration();
            //     const currentTime = Math.floor(results.progress) / 1000;
            //     console.info(`Duration is: ${duration} and Video progress is ${currentTime} seconds`);
            //     if(duration >= currentTime){
            //         player.currentTime(currentTime);
            //     }else {
            //         player.currentTime(duration - 1);
            //     }
            // }else {
            //     console.info(`Video progress is ${results.progress}`);
            // }

        }).fail((e) => {
            console.log(e)
        });
    }

    /**
     * On Load meta data event and listener
     * @param player
     * @param html5player
     */
    const html5playerOnLoadSingleVideoMetaData = (player, html5player) => {
        const cm = html5player.cmid;
        const video_id = html5player.video_id;
        player.on('loadedmetadata', function(e){
            console.info('Single video player meta data loaded...')
            get_single_video_course_module_progress(player,html5player);
        });
    }

    /**
     * On load playlislt meta data
     * @param player
     * @param html5player
     */
    const html5playerOnLoadPlaylistMetaData = (player, html5player) => {
        let playlists  = null;
        player.on('loadedmetadata',(e) => {
            console.info('playlist videos player meta data loaded...');
            get_playlist_video_progress(player, html5player);
            playlists = player.playlist();
            const currentItem = player.playlist.currentItem();
            // console.log(playlists);
            // console.log(currentItem);
            // console.log(playlists[currentItem]);

            // if (player.playlist.contains(currentItem)){
            //     console.log(playlists[currentItem]);
            // }
        });
    }

    /**
     * Event listener for single video.
     * @param player
     * @param html5player
     */
    const html5playerOnPlaySingleVideo = (player,html5player) => {
        html5PlayerGenericPlayerEventListener(player, html5player);
    }

    /**
     * Event listener for playlist.
     * @param player
     * @param html5player
     */
    const html5playerOnPlayPlaylist = (player, html5player) => {
        html5PlayerGenericPlayerEventListener(player, html5player);

        player.on('beforeplaylistitem', e => {
            console.log(`Event: beforeplaylistitem -> Switching to new video ...`);
            player.clearInterval(interval) ;
        });
    }

    // const initBrightCovePlayer = (course, cm, accountId, playerId, video_id) => {
    const initBrightCovePlayer = (html5player) => {
        html5player = JSON.parse(html5player);
        // Make brightcove js in Require js module as bc.
        loadBrightCoveJs(html5player.account_id, html5player.player_id);

        require(['bc'], function(bc) {
            console.info(`Brightcove player js loaded...`);
            // Tracking is enabled for only student.
            if (html5player.is_student ){
                const myPlayer = videojs.getPlayer(`brightcove-player-${html5player.player_id}`);

                if (html5player.video_type == 1){
                    // Do meta loaded stuffs here.
                    console.info('User is a student and Video type single video...');
                    html5playerOnLoadSingleVideoMetaData(myPlayer, html5player);
                    // Do Start playing stuffs here.
                    html5playerOnPlaySingleVideo(myPlayer,html5player);
                }else if( html5player.video_type == 2) {
                    console.info('User is a student and Video type playlists video...');
                    html5playerOnLoadPlaylistMetaData(myPlayer, html5player);
                    html5playerOnPlayPlaylist(myPlayer, html5player);
                }
            }
        });
    }

    return {
       init: initBrightCovePlayer
   }
});