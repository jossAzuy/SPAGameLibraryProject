import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { getGames } from '../services/gameService'

const initialFilters = {
  search: '',
  genre: '',
  platform: '',
  release_year: '',
  rating_min: '',
  sort_by: 'title',
  sort_direction: 'asc',
  per_page: 9,
}

export default function GamesPage() {
  const [games, setGames] = useState([])
  const [filters, setFilters] = useState(initialFilters)
  const [appliedFilters, setAppliedFilters] = useState(initialFilters)
  const [meta, setMeta] = useState(null)
  const [isLoading, setIsLoading] = useState(true)
  const [error, setError] = useState(null)

  useEffect(() => {
    async function loadGames() {
      try {
        setIsLoading(true)
        setError(null)

        const response = await getGames(appliedFilters)

        setGames(response.data ?? [])
        setMeta(response.meta ?? null)
      } catch (requestError) {
        console.error(requestError)
        setGames([])
        setMeta(null)
        setError('No fue posible cargar los juegos.')
      } finally {
        setIsLoading(false)
      }
    }

    loadGames()
  }, [appliedFilters])

  function handleChange(event) {
    const { name, value } = event.target

    setFilters((currentFilters) => ({
      ...currentFilters,
      [name]: value,
    }))
  }

  function handleSubmit(event) {
    event.preventDefault()

    setAppliedFilters({
      ...filters,
      page: 1,
    })
  }

  function handleClearFilters() {
    setFilters(initialFilters)
    setAppliedFilters(initialFilters)
  }

  function handlePageChange(page) {
    setAppliedFilters((currentFilters) => ({
      ...currentFilters,
      page,
    }))
  }

  return (
    <section className="mx-auto min-h-[calc(100vh-137px)] max-w-6xl px-6 py-12">
      <div>
        <h1 className="text-3xl font-bold">
          Catálogo de juegos
        </h1>

        <p className="mt-3 text-slate-400">
          Explora y filtra los juegos disponibles en MongoDB.
        </p>
      </div>

      <form
        onSubmit={handleSubmit}
        className="mt-8 rounded-xl border border-slate-800 bg-slate-900 p-5"
      >
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          <div className="lg:col-span-2">
            <label
              htmlFor="search"
              className="block text-sm font-medium text-slate-200"
            >
              Buscar
            </label>

            <input
              id="search"
              name="search"
              type="search"
              value={filters.search}
              onChange={handleChange}
              placeholder="Título, desarrollador o descripción"
              className="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950 px-4 py-3 text-slate-100 outline-none transition focus:border-sky-500"
            />
          </div>

          <div>
            <label
              htmlFor="genre"
              className="block text-sm font-medium text-slate-200"
            >
              Género
            </label>

            <select
              id="genre"
              name="genre"
              value={filters.genre}
              onChange={handleChange}
              className="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950 px-4 py-3 text-slate-100 outline-none transition focus:border-sky-500"
            >
              <option value="">Todos</option>
              <option value="Acción">Acción</option>
              <option value="Aventura">Aventura</option>
              <option value="Estrategia">Estrategia</option>
              <option value="Indie">Indie</option>
              <option value="Rol">Rol</option>
              <option value="Free to Play">Free to Play</option>
            </select>
          </div>

          <div>
            <label
              htmlFor="platform"
              className="block text-sm font-medium text-slate-200"
            >
              Plataforma
            </label>

            <select
              id="platform"
              name="platform"
              value={filters.platform}
              onChange={handleChange}
              className="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950 px-4 py-3 text-slate-100 outline-none transition focus:border-sky-500"
            >
              <option value="">Todas</option>
              <option value="PC">PC</option>
              <option value="macOS">macOS</option>
              <option value="Linux">Linux</option>
            </select>
          </div>

          <div>
            <label
              htmlFor="release_year"
              className="block text-sm font-medium text-slate-200"
            >
              Año
            </label>

            <input
              id="release_year"
              name="release_year"
              type="number"
              min="1970"
              max="2100"
              value={filters.release_year}
              onChange={handleChange}
              placeholder="Ejemplo: 2023"
              className="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950 px-4 py-3 text-slate-100 outline-none transition focus:border-sky-500"
            />
          </div>

          <div>
            <label
              htmlFor="rating_min"
              className="block text-sm font-medium text-slate-200"
            >
              Rating mínimo
            </label>

            <input
              id="rating_min"
              name="rating_min"
              type="number"
              min="0"
              max="10"
              step="0.1"
              value={filters.rating_min}
              onChange={handleChange}
              placeholder="Ejemplo: 8"
              className="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950 px-4 py-3 text-slate-100 outline-none transition focus:border-sky-500"
            />
          </div>

          <div>
            <label
              htmlFor="sort_by"
              className="block text-sm font-medium text-slate-200"
            >
              Ordenar por
            </label>

            <select
              id="sort_by"
              name="sort_by"
              value={filters.sort_by}
              onChange={handleChange}
              className="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950 px-4 py-3 text-slate-100 outline-none transition focus:border-sky-500"
            >
              <option value="title">Título</option>
              <option value="release_year">Año</option>
              <option value="rating">Rating</option>
              <option value="created_at">Fecha de registro</option>
            </select>
          </div>

          <div>
            <label
              htmlFor="sort_direction"
              className="block text-sm font-medium text-slate-200"
            >
              Dirección
            </label>

            <select
              id="sort_direction"
              name="sort_direction"
              value={filters.sort_direction}
              onChange={handleChange}
              className="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950 px-4 py-3 text-slate-100 outline-none transition focus:border-sky-500"
            >
              <option value="asc">Ascendente</option>
              <option value="desc">Descendente</option>
            </select>
          </div>
        </div>

        <div className="mt-5 flex flex-col gap-3 sm:flex-row">
          <button
            type="submit"
            disabled={isLoading}
            className="rounded-lg bg-sky-600 px-6 py-3 font-semibold text-white transition hover:bg-sky-500 disabled:cursor-not-allowed disabled:opacity-60"
          >
            Aplicar filtros
          </button>

          <button
            type="button"
            onClick={handleClearFilters}
            className="rounded-lg border border-slate-700 px-6 py-3 font-semibold text-slate-200 transition hover:border-slate-500 hover:bg-slate-800"
          >
            Limpiar filtros
          </button>
        </div>
      </form>

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
          No se encontraron juegos con los filtros seleccionados.
        </p>
      )}

      {!isLoading && !error && games.length > 0 && (
        <>
          <div className="mt-8 flex flex-wrap items-center justify-between gap-3 text-sm text-slate-400">
            <span>
              {meta?.total ?? games.length} juego
              {(meta?.total ?? games.length) !== 1 ? 's' : ''}
            </span>

            {meta?.current_page && meta?.last_page && (
              <span>
                Página {meta.current_page} de {meta.last_page}
              </span>
            )}
          </div>

          <div className="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
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
                    <div className="flex items-start justify-between gap-3">
                      <h2 className="text-xl font-semibold transition-colors group-hover:text-sky-400">
                        {game.title}
                      </h2>

                      {game.rating !== null &&
                        game.rating !== undefined && (
                          <span className="shrink-0 rounded-full bg-slate-800 px-3 py-1 text-xs text-slate-300">
                            {game.rating}/10
                          </span>
                        )}
                    </div>

                    {game.description && (
                      <p className="mt-3 line-clamp-3 text-sm text-slate-400">
                        {game.description}
                      </p>
                    )}

                    <div className="mt-4 flex flex-wrap gap-2">
                      {game.genres?.slice(0, 3).map((genre) => (
                        <span
                          key={genre}
                          className="rounded-full border border-slate-700 px-2.5 py-1 text-xs text-slate-300"
                        >
                          {genre}
                        </span>
                      ))}
                    </div>

                    <div className="mt-5 flex items-center justify-between text-sm text-slate-400">
                      <span>
                        {game.release_year ?? 'Año desconocido'}
                      </span>

                      <span>
                        {game.platforms?.join(', ') || 'Sin plataforma'}
                      </span>
                    </div>
                  </div>
                </article>
              </Link>
            ))}
          </div>

          {meta?.last_page > 1 && (
            <div className="mt-10 flex items-center justify-center gap-3">
              <button
                type="button"
                disabled={meta.current_page <= 1 || isLoading}
                onClick={() =>
                  handlePageChange(meta.current_page - 1)
                }
                className="rounded-lg border border-slate-700 px-4 py-2 text-slate-200 transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
              >
                Anterior
              </button>

              <span className="text-sm text-slate-400">
                {meta.current_page} / {meta.last_page}
              </span>

              <button
                type="button"
                disabled={
                  meta.current_page >= meta.last_page || isLoading
                }
                onClick={() =>
                  handlePageChange(meta.current_page + 1)
                }
                className="rounded-lg border border-slate-700 px-4 py-2 text-slate-200 transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
              >
                Siguiente
              </button>
            </div>
          )}
        </>
      )}
    </section>
  )
}