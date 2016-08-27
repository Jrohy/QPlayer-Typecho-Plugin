(function($){
	// Settings
	var repeat = localStorage.repeat || 0,
		shuffle = localStorage.shuffle || 'false',
		volume = localStorage.volume || 0.5,
		continous = true,
		isFirstPlay = true;
	// Load playlist
	for (var i=0; i<playlist.length; i++){
		var item = playlist[i];
		$('#playlist').append('<li class="lib"><strong style="margin-left: 5px;">'+item.title+'</strong><span style="float: right;" class="artist">'+item.artist+'</span></li>');
	}

	var time = new Date(),
		currentTrack = shuffle === 'true' ? time.getTime() % playlist.length : 0,
		trigger = false,
		audio, timeout, isPlaying, playCounts;

	var play = function(){
		audio.play();
		if (isRotate) {
			$("#player .cover img").css("animation","9.8s linear 0s normal none infinite rotate");
	    }
		$('.playback').addClass('playing');
		timeout = setInterval(updateProgress, 500);
		isPlaying = true;
		if(isExceedTag()) {
			if (isFirstPlay) {
				initMarquee();
				isFirstPlay = false;
			} else {
				$('.marquee').marquee('resume');
		    }
	    }
	}

	var pause = function(){
		audio.pause();
		$("#player .cover img").css("animation-play-state","paused");
		$('.playback').removeClass('playing');
		clearInterval(updateProgress);
		isPlaying = false;
		if(isExceedTag()) {
			$('.marquee').marquee('pause');
		}
	}

	// Update progress
	var setProgress = function(value){
		var currentSec = parseInt(value%60) < 10 ? '0' + parseInt(value%60) : parseInt(value%60),
			ratio = value / audio.duration * 100;

		$('.timer').html(parseInt(value/60)+':'+currentSec);
	}

	var updateProgress = function(){
		setProgress(audio.currentTime);
	}

	// Switch track
	var switchTrack = function(i){
		if (i < 0){
			track = currentTrack = playlist.length - 1;
		} else if (i >= playlist.length){
			track = currentTrack = 0;
		} else {
			track = i;
		}

		isFirstPlay = true;
		$('audio').remove();
		loadMusic(track);
		play();
	}

	// Shuffle
	var shufflePlay = function(){
		var time = new Date(),
			lastTrack = currentTrack;
		currentTrack = time.getTime() % playlist.length;
		if (lastTrack == currentTrack) ++currentTrack;
		switchTrack(currentTrack);
	}

	// Fire when track ended
	var ended = function(){
		pause();
		audio.currentTime = 0;
		playCounts++;
		if (continous == true) isPlaying = true;
		if (repeat == 1){
			play();
		} else {
			if (shuffle === 'true'){
				shufflePlay();
			} else {
				if (repeat == 2){
					switchTrack(++currentTrack);
				} else {
					if (currentTrack < playlist.length) switchTrack(++currentTrack);
				}
			}
		}
	}

	var beforeLoad = function(){
		var endVal = this.seekable && this.seekable.length ? this.seekable.end(0) : 0;
	}

	// Fire when track loaded completely
	var afterLoad = function(){
		if (autoplay == true) play();
	}

	// Load track
	var loadMusic = function(i){
		var item = playlist[i],
		newaudio = $('<audio>').html('<source src="'+item.mp3+'"><source src="'+item.ogg+'">').appendTo('#player');
		$('.cover').html('<img src="'+item.cover+'" alt="'+item.album+'">');
		$('.musicTag').html('<strong>'+item.title+'</strong><span> - </span><span class="artist">'+item.artist+'</span>');
		$('#playlist li').removeClass('playing').eq(i).addClass('playing');
		audio = newaudio[0];
		audio.volume = volume;
		audio.addEventListener('progress', beforeLoad, false);
		audio.addEventListener('durationchange', beforeLoad, false);
		audio.addEventListener('canplay', afterLoad, false);
		audio.addEventListener('ended', ended, false);
	}

	loadMusic(currentTrack);

	$('.playback').on('click', function(){
		if ($(this).hasClass('playing')){
			pause();
		} else {
			play();
		}
	});

	$('.rewind').on('click', function(){
		if (shuffle === 'true'){
			shufflePlay();
		} else {
			switchTrack(--currentTrack);
		}
	});

	$('.fastforward').on('click', function(){
		if (shuffle === 'true'){
			shufflePlay();
		} else {
			switchTrack(++currentTrack);
		}
	});
	$('#playlist li').each(function(i){
		var _i = i;
		$(this).on('click', function(){
			switchTrack(_i);
			currentTrack = _i;
		});
	});

	$('#QPlayer .liebiao,#QPlayer .liebiao').on('click', function(){
		$('#playlist').slideToggle(350);
	});		

	$("#QPlayer .ssBtn").on('click', function(){
		var mA = $("#QPlayer");
		if(mA.css('left')=="-250px"){			
			mA.animate({ left: 0 }, "350");
		    $(' .ssBtn .adf').addClass('on')
		}else{
			mA.animate({ left: "-250px" }, "350");			
            $(' .ssBtn .adf').removeClass('on') 
            1 == QPlayer_var.autoplay && $(a).trigger("play")	
		}
	}); 

})(jQuery);



function initMarquee(){
	$('.marquee').marquee({
	    //speed in milliseconds of the marquee
	    duration: 15000,
	    //gap in pixels between the tickers
	    gap: 50,
	    //time in milliseconds before the marquee will start animating
	    delayBeforeStart: 0,
	    //'left' or 'right'
	    direction: 'left',
	    //true or false - should the marquee be duplicated to show an effect of continues flow
	    duplicated: true
	});
}

//检测标题和作者信息总宽度是否超出播放器，超过则返回true开启跑马灯
function isExceedTag() {
	var isExceedTag = false;
	if ($('.musicTag strong').width() + $('.musicTag span').width() + $('.musicTag .artist').width() > $('.musicTag').width()) {
	    isExceedTag = true;
	}
	return isExceedTag;
}