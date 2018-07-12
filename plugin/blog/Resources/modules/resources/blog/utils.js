import {constants} from '#/plugin/blog/resources/blog/constants.js'

function getCommentsNumber(canEdit, publisedNumber, unpublishedNumber) {
  return canEdit ? publisedNumber + unpublishedNumber : publisedNumber
}

function splitArray(array){
  return array.split(',').map(item => item.trim())
}

function cleanTag(mode, tag){
  if(mode === constants.TAGCLOUD_TYPE_CLASSIC_NUM) {
    tag = tag.replace(/ *\([0-9+]*\) */g, '')
  }

  return tag
}

export {
  getCommentsNumber,
  splitArray,
  cleanTag
}