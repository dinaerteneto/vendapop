import { useState, useEffect } from 'react';
import api from '../services/api';

export function useSpotCounter() {
  const [remaining, setRemaining] = useState<number | null>(null);
  const [total, setTotal] = useState<number | null>(null);
  const [nextReplenish, setNextReplenish] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isError, setIsError] = useState(false);

  useEffect(() => {
    let cancelled = false;

    async function fetch() {
      setIsLoading(true);
      setIsError(false);
      try {
        const res = await api.get('/spots/remaining');
        if (!cancelled) {
          setRemaining(res.data.remaining ?? 0);
          setTotal(res.data.total ?? 0);
          setNextReplenish(res.data.next_replenish ?? null);
        }
      } catch {
        if (!cancelled) {
          setIsError(true);
        }
      } finally {
        if (!cancelled) {
          setIsLoading(false);
        }
      }
    }

    fetch();

    return () => {
      cancelled = true;
    };
  }, []);

  return { remaining, total, nextReplenish, isLoading, isError };
}
