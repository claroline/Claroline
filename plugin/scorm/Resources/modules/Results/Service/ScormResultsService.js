/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*global Routing*/
/*global Translator*/

export default class ScormResultsService {

  constructor($http) {
    this.$http = $http
    this.resourceNodeId = ScormResultsService._getGlobal('resourceNodeId')
    this.type = ScormResultsService._getGlobal('type')
    this.scos = JSON.parse(ScormResultsService._getGlobal('scos'))
    this.scosTrackings = {}
    this.trackings = this.formatTrackingsDatas(ScormResultsService._getGlobal('trackings'))
    this.trackingsDetails = {}
  }

  getType () {
    return this.type
  }

  getScos () {
    return this.scos
  }

  getTrackings () {
    return this.trackings
  }

  getScosTrackings () {
    return this.scosTrackings
  }

  getTrackingsDetails () {
    return this.trackingsDetails
  }

  formatTrackingsDatas (serializedDatas) {
    let datas = JSON.parse(serializedDatas)
    datas.forEach(t => {
      t['userId'] = t['user']['id']
      t['userFirstName'] = t['user']['firstName']
      t['userLastName'] = t['user']['lastName']
      t['userUsername'] = t['user']['username']

      if (this.type === 'scorm12') {
        t['transBestLessonStatus'] = Translator.trans(t['bestLessonStatus'], {}, 'scorm')
        t['transLessonStatus'] = Translator.trans(t['lessonStatus'], {}, 'scorm')
        t['convertedSessionTime'] = this.convertScorm12Time(t['sessionTime'])
        t['convertedTotalTime'] = this.convertScorm12Time(t['totalTime'])
      } else if (this.type === 'scorm2004') {
        t['transCompletionStatus'] = Translator.trans(t['completionStatus'], {}, 'scorm')
        t['transSuccessStatus'] = Translator.trans(t['successStatus'], {}, 'scorm')
        t['convertedSessionTime'] = this.convertScorm2004Time(t['details']['cmi.session_time'])
        t['convertedTotalTime'] = this.convertScorm2004Time(t['totalTime'])
      }
      this.sortTrackingBySco(t)
    })

    return datas
  }

  sortTrackingBySco (tracking) {
    const scoId = tracking['sco']['id']

    if (!this.scosTrackings[scoId]) {
      this.scosTrackings[scoId] = []
    }
    this.scosTrackings[scoId].push(tracking)
  }

  convertScorm12Time (time) {
    if (time === undefined || time === null) {
      return null
    }
    let remainingTime = Math.floor(time / 100)
    const hours = Math.floor(remainingTime / 3600)
    remainingTime %= 3600
    const minutes = Math.floor(remainingTime / 60)
    remainingTime %= 60
    const minutesTxt = minutes > 9 ? minutes : `0${minutes}`
    const secundsTxt = remainingTime > 9 ? remainingTime : `0${remainingTime}`

    return `${hours}:${minutesTxt}:${secundsTxt}`
  }

  convertScorm2004Time (time) {
    if (time === undefined || time === null) {
      return null
    }
    const pattern = /T([0-9]+H)?([0-9]+M)?([0-9]+S)?$/
    const formattedTime =  this.formatScorm2004Date(time)
    const matches = formattedTime.match(pattern)
    const hours = matches[1] === undefined ? 0 : parseInt(matches[1].replace(/H/, ''))
    const minutes = matches[2] === undefined ? 0 : parseInt(matches[2].replace(/M/, ''))
    const secunds = matches[3] === undefined ? 0 : parseInt(matches[3].replace(/S/, ''))
    const minutesTxt = minutes > 9 ? minutes : `0${minutes}`
    const secundsTxt = secunds > 9 ? secunds : `0${secunds}`

    return `${hours}:${minutesTxt}:${secundsTxt}`
  }

  formatScorm12Date (date) {
    const formattedDate = date.replace(/\..*$/, '')

    return formattedDate
  }

  formatScorm2004Date (date) {
    const formattedDate = date.replace(/\..*S$/, 'S')

    return formattedDate
  }

  loadTrackingDetails (userId, trackingId, scoId) {
    if (this.trackingsDetails[trackingId] === undefined) {
      const url = Routing.generate('claro_scorm_get_tracking_details', {user: userId, resourceNode: this.resourceNodeId, scoId: scoId})
      this.$http.get(url).then(d => {
        if (d['status'] === 200) {
          this.trackingsDetails[trackingId] = this.formatDetailsDatas(d['data'])
        }
      })
    }
  }

  formatDetailsDatas (detailsDatas) {
    detailsDatas.forEach(d => {
      d['formattedDate'] = this.formatScorm12Date(d['dateLog']['date'])

      if (this.type === 'scorm12') {
        d['transLessonStatus'] = Translator.trans(d['details']['lessonStatus'], {}, 'scorm')
        d['convertedSessionTime'] = this.convertScorm12Time(d['details']['sessionTime'])
        d['convertedTotalTime'] = this.convertScorm12Time(d['details']['totalTime'])
      } else if (this.type === 'scorm2004') {
        d['transCompletionStatus'] = Translator.trans(d['details']['cmi.completion_status'], {}, 'scorm')
        d['transSuccessStatus'] = Translator.trans(d['details']['cmi.success_status'], {}, 'scorm')
        d['convertedSessionTime'] = this.convertScorm2004Time(d['details']['cmi.session_time'])
        d['convertedTotalTime'] = this.convertScorm2004Time(d['details']['cmi.total_time'])
      }
    })

    return detailsDatas
  }

  static _getGlobal (name) {
    if (typeof window[name] === 'undefined') {
      throw new Error(
        `Expected ${name} to be exposed in a window.${name} variable`
      )
    }

    return window[name]
  }
}