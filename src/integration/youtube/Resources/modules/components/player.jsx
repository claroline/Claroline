import React, {Component}  from 'react'
import {PropTypes as T} from 'prop-types'
import {Video as VideoTypes} from '#/integration/youtube/prop-types'

class YouTubePlayer extends Component {
  constructor(props) {
    super(props)

    this.player = null
    this.timer = null
    this.resumed = false

    this.onTimer = this.onTimer.bind(this)
  }

  componentDidMount() {
    this.player = new window.YT.Player('youtube-player', {
      width: '560',
      height: '315',
      videoId: this.props.video.videoId,
      playerVars: {
        playlist: this.props.video.videoId,
        autoplay: this.props.video.autoplay ? 1 : 0,
        loop: this.props.video.looping ? 1 : 0,
        controls: this.props.video.controls ? 2 : 0,
        start: this.props.video.timecodeStart,
        end: this.props.video.timecodeEnd
      },
      events : {
        onStateChange: (event) => {
          switch (event.data) {
            case window.YT.PlayerState.PLAYING:
              if(!this.resumed && this.props.video.resume) {
                this.player.seekTo(event.target.getDuration() * (this.props.progression / 100) - 5, true)
                this.resumed = true
              }
              this.props.onPlay(event.target.getCurrentTime(), event.target.getDuration())
              this.timer = setInterval( this.onTimer, 1000)
              break
            case window.YT.PlayerState.PAUSED:
              this.props.onPause(event.target.getCurrentTime(), event.target.getDuration())
              clearInterval(this.timer)
              break
          }
        }
      }
    })
  }

  componentWillUnmount() {
    if (this.player && this.props.onPause) {
      this.props.onPause( this.player.getCurrentTime(), this.player.getDuration() )
    }
  }

  onTimer() {
    this.props.onTimeUpdate( this.player.getCurrentTime(), this.player.getDuration() )
  }

  render() {
    return (
      <div className="youtube-player-container">
        <div id={'youtube-player'} />
      </div>
    )
  }
}

YouTubePlayer.propTypes = {
  video: T.shape( VideoTypes.propTypes ).isRequired,
  progression: T.number.isRequired,
  onPlay: T.func,
  onPause: T.func,
  onTimeUpdate: T.func
}

export {
  YouTubePlayer
}
