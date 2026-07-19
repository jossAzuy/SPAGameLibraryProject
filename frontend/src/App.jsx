import { BrowserRouter, Route, Routes } from 'react-router-dom'
import MainLayout from './layouts/MainLayout'
import GamesPage from './pages/GamesPage'
import HomePage from './pages/HomePage'
import NotFoundPage from './pages/NotFoundPage'
import SemanticSearchPage from './pages/SemanticSearchPage'
import GameDetailPage from './pages/GameDetailPage'

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route element={<MainLayout />}>
          <Route index element={<HomePage />} />

          <Route path="games" element={<GamesPage />} />

          <Route
            path="games/:id"
            element={<GameDetailPage />}
          />

          <Route
            path="semantic-search"
            element={<SemanticSearchPage />}
          />

          <Route path="*" element={<NotFoundPage />} />
        </Route>
      </Routes>
    </BrowserRouter>
  )
}