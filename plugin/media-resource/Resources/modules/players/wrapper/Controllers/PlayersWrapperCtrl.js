
class PlayersWrapperCtrl {

  constructor(configService) {
    this.configService = configService
    this.playmodes = this.configService.getAvailablePlayModes()
  }

  isActiveMode(){
    return this.resource.options.mode === this.playmodes.find(el => el.key === 'CONTINUOUS_ACTIVE').value
  }

  isLiveMode(){
    return this.resource.options.mode === this.playmodes.find(el => el.key === 'CONTINUOUS_LIVE').value
  }

  isFreeMode(){
    return this.resource.options.mode === this.playmodes.find(el => el.key === 'FREE').value
  }

  isPauseMode(){
    return this.resource.options.mode === this.playmodes.find(el => el.key === 'CONTINUOUS_PAUSE').value
  }

  isScriptedMode(){
    return this.resource.options.mode === this.playmodes.find(el => el.key === 'SCRIPTED_ACTIVE').value
  }
}

PlayersWrapperCtrl.$inject = [
  'configService'
]
export default PlayersWrapperCtrl
