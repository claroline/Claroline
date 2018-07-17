import {makeReducer} from '#/main/app/store/reducer'
import cloneDeep from 'lodash/cloneDeep'
import isEmpty from 'lodash/isEmpty'
import difference from 'lodash/difference'
import {
  ADD_TAGS,
  ADD_AUTHOR,
  LOAD_TAGS,
  LOAD_AUTHORS
} from '#/plugin/blog/resources/blog/toolbar/store/actions'

const reducer = {
  tags: makeReducer({}, {
    [LOAD_TAGS]: (state, action) => action.tags,
    [ADD_TAGS]: (state, action) => {
      let originalTagsArray = action.originalTags.split(',').map(item =>item.trim())
      let tagsArray = action.tags.split(',').map(item =>item.trim())
      let toRemove = difference(originalTagsArray, tagsArray)
      let toAdd = difference(tagsArray, originalTagsArray)

      const tags = cloneDeep(state)
      //remove old tags
      for (let tag of toRemove) {
        if(!isEmpty(tag)){
          if(tags[tag]){
            tags[tag]--
            if(tags[tag] <= 0){
              delete tags[tag]
            }
          }
        }
      }
      //add new tags
      for (let tag of toAdd) {
        if(!isEmpty(tag)){
          if(tags[tag]){
            tags[tag]++
          }else{
            tags[tag] = 1
          }
        }
      }

      return tags
    }
  }),
  authors: makeReducer({}, {
    [LOAD_AUTHORS]: (state, action) => action.authors,
    [ADD_AUTHOR]: (state, action) => {
      const authors = cloneDeep(state)
      const authorIndex = authors.findIndex(e => e.id === action.author.id)
      if(authorIndex === -1){
        authors.push(action.author)
      }

      return authors
    }
  })
}

export {
  reducer
}