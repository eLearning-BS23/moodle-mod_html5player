define(['jquery','core/ajax'], function ($, Ajax) {

    let interval;

    let onPageLoaded;

    const VIDEO_TYPE_SINGLE_VIDEO = 1;

    const VIDEO_TYPE_PLAYLIST = 2;

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
     * Common event listener for brightcove Player.
     * @param player
     * @param html5player
     */
    const html5PlayerGenericPlayerEventListener = (player, html5player) => {
        const cm = html5player.cmid;
        let video_id = html5player.video_id;

        player.on('play',(e)=> {
            video_id = player.mediainfo.id;
            console.info(`Video: ${player.mediainfo.id} started playing...`)
            interval = player.setInterval(function(){
                const currentTime = Math.ceil(player.currentTime());
                console.log(`Video playing. Total length: ${player.duration()}. Video current progress is : ${player.currentTime()}`)
                set_course_module_progress(html5player, cm,video_id,currentTime)
            }, +html5player.progress_interval);

        })

        player.on('pause',(e)=>{
            player.clearInterval(interval);
        })
    }

    /**
     * @param player
     * @param results
     */
    const html5playerSetProgress = (player, results) => {
        let progress = results?.progress
        if (progress){
            const duration = player.duration();
            const currentTime = Math.floor(progress) / 1000;
            console.info(`Duration is: ${duration} and Video progress is ${currentTime} seconds`);
            if(duration >= currentTime){
                player.currentTime(currentTime);
            }
            // else {
            //     player.currentTime(duration - 1);
            // }
        }else {
            console.info(`Video progress is ${progress}`);
        }
    }

    /**
     * Set course module video progress
     * @param id
     * @param videoid
     * @param progress
     * @param ended
     */
    const set_course_module_progress = (html5player, id, videoid, progress, ended=false) => {
        let promise;

        promise = Ajax.call([{
            methodname: 'mod_html5player_set_module_progress',
            args: {
                id, // course module id.
                videoid, // Brightcove video id.
                progress, // Progress percentage
                ended, // Progress percentage
            }
        }]);

        promise[0].then(function(results) {
            console.info(`Video completed : ${results.completed}`);
            if (results.completed) {
                const toggledEvent = new CustomEvent('core_course:html5player_view_completed', {
                    bubbles: true,
                    detail: {
                        id: id,
                        activityname: html5player.name,
                        completed: results.completed,
                        withAvailability: '',
                    }
                });
                // Dispatch the manualCompletionToggled custom event.
                document.dispatchEvent(toggledEvent);
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
            html5playerSetProgress(player, results);
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

        promise[0].then(function(response) {
            console.info(`Fetched module progresses result from store`);
            if (response.progresses?.length > 0){
                const playlists = player.playlist();
                let result;
                if (onPageLoaded){
                    console.info('Current item progress set after first time dom load...');
                    result = response.progresses[0];
                    const index = playlists.findIndex(video => video.id == result.video_id);
                    if (index>=0){
                        player.playlist.currentItem(index);
                        html5playerSetProgress(player, result);
                        onPageLoaded = false;
                    }
                }else {
                    const currentItemIndex = player.playlist.currentItem();
                    const currentItem = playlists[currentItemIndex]
                    result = response.progresses.find(video => video.video_id == currentItem.id )
                    html5playerSetProgress(player, result);
                }

            }
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
        player.on('loadedmetadata', function(e){
            console.info('Single video player meta data loaded...')
            get_single_video_course_module_progress(player,html5player);
        });
    }

    /**
     * Event listener for single video.
     * @param player
     * @param html5player
     */
    const html5playerOnPlaySingleVideo = (player,html5player) => {
        html5PlayerGenericPlayerEventListener(player, html5player);

        player.on('ended',(e)=>{
            const currentTime = Math.ceil( player.duration());
            console.log(`Video ended... Video id: ${player.mediainfo.id}, Duration: ${player.duration()}`)
            set_course_module_progress(html5player, html5player.cmid,html5player.video_id,currentTime,true)
            player.clearInterval(interval);
        })
    }

    /**
     * On load playlists meta data
     * @param player
     * @param html5player
     * @param onpageload
     */
    const html5playerOnLoadPlaylistMetaData = (player, html5player) => {
        player.on('loadedmetadata',(e) => {
            console.info('playlist videos player meta data loaded...');
            get_playlist_video_progress(player, html5player);
        });
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

        player.on('ended',(e)=>{
            const currentTime = Math.ceil( player.duration());
            console.log(`Video ended...`)
            set_course_module_progress(html5player, html5player.cmid,player.mediainfo.id,currentTime, true)
            player.clearInterval(interval);
            const nextVideo = player.playlist.next();
            console.info(`Start playing to next video : ${nextVideo.id}`)
        })
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

                if (html5player.video_type == VIDEO_TYPE_SINGLE_VIDEO){
                    // Do meta loaded stuffs here.
                    console.info('User is a student and Video type single video...');
                    html5playerOnLoadSingleVideoMetaData(myPlayer, html5player);
                    // Do Start playing stuffs here.
                    html5playerOnPlaySingleVideo(myPlayer,html5player);
                }else if( html5player.video_type == VIDEO_TYPE_PLAYLIST) {
                    console.info('User is a student and Video type playlists video...');
                    html5playerOnLoadPlaylistMetaData(myPlayer, html5player);
                    onPageLoaded = true;
                    html5playerOnPlayPlaylist(myPlayer, html5player);
                }
            }
        });
    }

    return {
       init: initBrightCovePlayer
   }
});