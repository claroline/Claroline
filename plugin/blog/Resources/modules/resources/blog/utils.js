function getCommentsNumber(canEdit, publisedNumber, unpublishedNumber) {
  return canEdit ? publisedNumber + unpublishedNumber : publisedNumber
}

function splitArray(array){
  return array.split(',').map(item => item.trim())
}

export {
  getCommentsNumber,
  splitArray
}