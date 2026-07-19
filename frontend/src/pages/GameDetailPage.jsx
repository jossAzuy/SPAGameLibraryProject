import { useEffect, useState } from "react";
import { Link, useParams } from "react-router-dom";
import { getGame } from "../services/gameService";

export default function GameDetailPage() {
  const { id } = useParams();

  const [game, setGame] = useState(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    async function loadGame() {
      try {
        const response = await getGame(id);

        setGame(response.data);
      } catch (requestError) {
        console.error(requestError);
        setError("No fue posible cargar la información del juego.");
      } finally {
        setIsLoading(false);
      }
    }

    loadGame();
  }, [id]);

  if (isLoading) {
    return (
      <section className="mx-auto min-h-[calc(100vh-137px)] max-w-6xl px-6 py-12">
        <p className="text-slate-400">Cargando juego...</p>
      </section>
    );
  }

  if (error) {
    return (
      <section className="mx-auto min-h-[calc(100vh-137px)] max-w-6xl px-6 py-12">
        <div className="rounded-lg border border-red-900 bg-red-950/40 p-4 text-red-300">
          {error}
        </div>

        <Link
          to="/games"
          className="mt-6 inline-block text-sky-400 hover:text-sky-300"
        >
          Volver al catálogo
        </Link>
      </section>
    );
  }

  if (!game) {
    return (
      <section className="mx-auto min-h-[calc(100vh-137px)] max-w-6xl px-6 py-12">
        <p className="text-slate-400">No se encontró el juego solicitado.</p>
      </section>
    );
  }

  return (
    <section className="mx-auto min-h-[calc(100vh-137px)] max-w-6xl px-6 py-12">
      <Link
        to="/games"
        className="inline-flex text-sm text-sky-400 hover:text-sky-300"
      >
        ← Volver al catálogo
      </Link>

      <article className="mt-6 overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">
        {game.cover_url && (
          <img
            src={game.cover_url}
            alt={`Portada de ${game.title}`}
            className="aspect-[21/9] w-full object-cover"
          />
        )}

        <div className="grid gap-8 p-6 md:grid-cols-[2fr_1fr] md:p-8">
          <div>
            <h1 className="text-3xl font-bold text-white">{game.title}</h1>

            {game.description && (
              <p className="mt-5 leading-7 text-slate-300">
                {game.description}
              </p>
            )}

            {game.genres?.length > 0 && (
              <div className="mt-8">
                <h2 className="text-lg font-semibold text-white">Géneros</h2>

                <div className="mt-3 flex flex-wrap gap-2">
                  {game.genres.map((genre) => (
                    <span
                      key={genre}
                      className="rounded-full bg-slate-800 px-3 py-1 text-sm text-slate-300"
                    >
                      {genre}
                    </span>
                  ))}
                </div>
              </div>
            )}

            {game.tags?.length > 0 && (
              <div className="mt-8">
                <h2 className="text-lg font-semibold text-white">
                  Características
                </h2>

                <div className="mt-3 flex flex-wrap gap-2">
                  {game.tags.map((tag) => (
                    <span
                      key={tag}
                      className="rounded-full border border-slate-700 px-3 py-1 text-sm text-slate-400"
                    >
                      {tag}
                    </span>
                  ))}
                </div>
              </div>
            )}
          </div>

          <aside className="space-y-5 rounded-xl bg-slate-950/60 p-5">
            <GameInformation label="Desarrollador" value={game.developer} />

            <GameInformation label="Publisher" value={game.publisher} />

            <GameInformation
              label="Año de lanzamiento"
              value={game.release_year}
            />

            <GameInformation
              label="Calificación"
              value={
                game.rating !== null && game.rating !== undefined
                  ? `${game.rating} / 10`
                  : null
              }
            />

            {game.platforms?.length > 0 && (
              <div>
                <h2 className="text-sm font-medium text-slate-500">
                  Plataformas
                </h2>

                <p className="mt-1 text-slate-200">
                  {game.platforms.join(" · ")}
                </p>
              </div>
            )}

            {game.steam_url && (
              <a
                href={game.steam_url}
                target="_blank"
                rel="noreferrer"
                className="block rounded-lg bg-sky-600 px-4 py-3 text-center font-medium text-white transition hover:bg-sky-500"
              >
                Ver en Steam
              </a>
            )}
          </aside>
        </div>
      </article>
    </section>
  );
}

function GameInformation({ label, value }) {
  if (value === null || value === undefined || value === "") {
    return null;
  }

  return (
    <div>
      <h2 className="text-sm font-medium text-slate-500">{label}</h2>

      <p className="mt-1 text-slate-200">{value}</p>
    </div>
  );
}
