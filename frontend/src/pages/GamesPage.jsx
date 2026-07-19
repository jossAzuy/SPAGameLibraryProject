import { Link } from 'react-router-dom'
import { useEffect, useState } from 'react'
import { getGames } from '../services/gameService'

export default function GamesPage() {
  const [games, setGames] = useState([])
  const [isLoading, setIsLoading] = useState(true)
  const [error, setError] = useState(null)

  useEffect(() => {
    async function loadGames() {
      try {
        const response = await getGames()

        setGames(response.data ?? [])
      } catch (requestError) {
        console.error(requestError)
        setError('No fue posible cargar los juegos.')
      } finally {
        setIsLoading(false)
      }
    }

    loadGames()
  }, [])

  return (
    <section className="mx-auto min-h-[calc(100vh-137px)] max-w-6xl px-6 py-12">
      <div>
        <h1 className="text-3xl font-bold">Catálogo de juegos</h1>

        <p className="mt-3 text-slate-400">
          Explora los juegos disponibles en la biblioteca.
        </p>
      </div>

      {isLoading && (
        <p className="mt-10 text-slate-400">
          Cargando juegos...
        </p>
      )}

      {error && (
        <div className="mt-10 rounded-lg border border-red-900 bg-red-950/40 p-4 text-red-300">
          {error}
        </div>
      )}

      {!isLoading && !error && games.length === 0 && (
        <p className="mt-10 text-slate-400">
          No hay juegos disponibles.
        </p>
      )}

      {!isLoading && !error && games.length > 0 && (
        <div className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {games.map((game) => (
            <Link
              key={game.id}
              to={`/games/${game.id}`}
              className="group block"
            >
              <article className="h-full overflow-hidden rounded-xl border border-slate-800 bg-slate-900 transition duration-200 group-hover:-translate-y-1 group-hover:border-sky-600">
                {game.cover_url && (
                  <img
                    src={game.cover_url}
                    alt={`Portada de ${game.title}`}
                    className="aspect-video w-full object-cover"
                    loading="lazy"
                  />
                )}

                <div className="p-5">
                  <h2 className="text-xl font-semibold transition-colors group-hover:text-sky-400">
                    {game.title}
                  </h2>

                  {game.description && (
                    <p className="mt-3 line-clamp-3 text-sm text-slate-400">
                      {game.description}
                    </p>
                  )}
                </div>
              </article>
            </Link>
          ))}
        </div>
      )}
    </section>
  )
}