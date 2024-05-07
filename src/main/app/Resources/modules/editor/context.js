import {createContext} from 'react'

const EditorContext = createContext({
  name: null,
  actions: [],
  styles: []
})

export {
  EditorContext
}
