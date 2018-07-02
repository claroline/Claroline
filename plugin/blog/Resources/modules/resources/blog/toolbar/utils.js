import {BlogCalendar} from '#/plugin/blog/resources/blog/toolbar/components/calendar.jsx'
import {Redactors} from '#/plugin/blog/resources/blog/toolbar/components/redactors.jsx'
import {Infobar} from '#/plugin/blog/resources/blog/toolbar/components/infobar.jsx'
import {Tags} from '#/plugin/blog/resources/blog/toolbar/components/tags.jsx'
import {Archives} from '#/plugin/blog/resources/blog/toolbar/components/archives.jsx'
import {Exporters} from '#/plugin/blog/resources/blog/toolbar/components/exporters.jsx'

function getComponentByPanelLabel(label) {
  if(label === 'infobar'){
    return Infobar
  }else if(label === 'tagcloud'){
    return Tags
  }else if(label === 'redactor'){
    return Redactors
  }else if(label === 'calendar'){
    return BlogCalendar
  }else if(label === 'rss'){
    return Exporters
  }else if(label === 'archives'){
    return Archives
  }
}

function moveItemInArray(arr, old_index, new_index) {
  if (new_index >= arr.length) {
    var k = new_index - arr.length + 1
    while (k--) {
      arr.push(undefined)
    }
  }
  arr.splice(new_index, 0, arr.splice(old_index, 1)[0])
}

export {
  getComponentByPanelLabel,
  moveItemInArray
}