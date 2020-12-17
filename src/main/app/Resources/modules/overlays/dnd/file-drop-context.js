import {createContext} from 'react'

/**
 * Defines the FileDropContext.
 *
 * It will become `true` when files are dragged over the browser window.
 * File dropzone can read this context value to be highlighted as soon as the files
 * enter the window (not only when the files are over the dropzone).
 *
 * NB. I should have used redux to expose this value. I use context because this is
 * the same pattern used to manage standard drag and drop.
 */
const FileDropContext = createContext(false)

export {
  FileDropContext
}
