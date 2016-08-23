class RegionsService {

  create(start, end) {
    let region = {
      uuid: this.createGuid(),
      start: Number(start),
      end: Number(end),
      note: '',
      helps: {
        backward: false,
        loop: false,
        rate: false,
        helpRegionUuid: '',
        helpLinks: this.createDefaultLinks(),
        helpTexts: this.createDefaultTexts()
      }
    }
    return region
  }

  createDefaultLinks(){
    let links = []
    for(let i = 0; i < 3; i++){
      links.push(
        {id:null, url:''}
      )
    }
    return links
  }

  createDefaultTexts(){
    let texts = []
    for(let i = 0; i < 3; i++){
      texts.push(
        {id:null, text:''}
      )
    }
    return texts
  }

  getRegionByUuid(searched, regions) {
    let result = regions.find(el => el.uuid === searched)
    return result
  }

  getRegionFromTime(time, regions) {
    const currentTime = Number(time)
    let result
    for (let region of regions) {
      if (region.start <= currentTime && region.end > currentTime) {
        result = region
        break
      }
    }
    return result
  }

  getNextRegion(time, regions) {
    let next
    let current = this.getRegionFromTime(time, regions)
      // find next region relatively to current
    for (let region of regions) {
      if (region.start === current.end) {
        next = region
      }
    }
    return next
  }

  getPrevRegion(time, regions) {
    let prev
    let current = this.getRegionFromTime(time, regions)
      // find next region relatively to current
    for (let region of regions) {
      if (region.end === current.start) {
        prev = region
      }
    }
    return prev
  }

  regionHasHelp(helps) {
    return helps && (helps.backward || helps.helpRegionUuid !== '' || helps.helpLinks.filter(el => el.url !== '').length > 0 || helps.helpTexts.filter(el => el.text !== '').length > 0 || helps.loop || helps.rate)
  }

  regionHasHelpTexts(helps) {
    return helps.helpTexts.filter(el => el.text !== '').length > 0
  }

  regionHasHelpLinks(helps) {
    return helps.helpLinks.filter(el => el.url !== '').length > 0
  }

  regionHasPlayHelps(helps) {
    return helps.loop || helps.backward || helps.rate || helps.helpRegionUuid !== ''
  }

  createGuid() {
    let uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
      let r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8)
      return v.toString(16)
    })
    return uuid.toUpperCase()
  }
  /**
   * remove html form region note for TTS use
   */
  removeHtml(string) {
    return string.replace(/<[^>]+>/g, '')
  }
}

export default RegionsService
