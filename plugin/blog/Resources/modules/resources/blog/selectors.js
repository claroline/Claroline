import {createSelector} from 'reselect'

const blog = state => state.blog

const countTags = createSelector(
  [blog],
  (blog) => blog.data.tags.reduce((obj, tag) => {
    if (!obj[tag]) {
      obj[tag] = 0
    }
    obj[tag]++
    return obj
  }, {})
)

const displayTagsFrequency = createSelector(
  [blog],
  (blog) => {
    let obj = {}
    Object.keys(blog.data.tags).map(function (keyName) {
      let value = blog.data.tags[keyName]
      obj[keyName + '(' + value + ')'] = value
    })

    return obj
  }
)

export const select = {
  countTags, displayTagsFrequency
}