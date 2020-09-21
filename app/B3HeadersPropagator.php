<?php

namespace App;

use OpenCensus\Trace\Propagator\PropagatorInterface;
use OpenCensus\Trace\SpanContext;

/**
 * @see https://github.com/openzipkin/b3-propagation
 */
class B3HeadersPropagator implements PropagatorInterface
{
    private const X_B3_TRACE_ID = 'X-B3-TraceId';
    private const X_B3_SPAN_ID = 'X-B3-SpanId';
    private const X_B3_SAMPLED = 'X-B3-Sampled';
    private const HTTP_X_B3_TRACE_ID = 'HTTP_X_B3_TRACEID';
    private const HTTP_X_B3_SPAN_ID = 'HTTP_X_B3_SPANID';
    private const HTTP_X_B3_SAMPLED = 'HTTP_X_B3_SAMPLED';
    private const HTTP_X_B3_FLAGS = 'HTTP_X_B3_FLAGS';

    /**
     * Extract the SpanContext from some container
     *
     * @param mixed $container
     * @return SpanContext
     */
    public function extract($container)
    {
        $traceId = $container[self::HTTP_X_B3_TRACE_ID] ?? null;
        $spanId = $container[self::HTTP_X_B3_SPAN_ID] ?? null;
        $sampled = $container[self::HTTP_X_B3_SAMPLED] ?? null;
        $flags = $container[self::HTTP_X_B3_FLAGS] ?? null;

        error_log('traceid: '.$traceId.' sampeled: '.$sampled);

        if (!$traceId || !$spanId) {
            return new SpanContext();
        }

        $enabled = null;

        if ($sampled !== null) {
            $enabled = ($sampled === '1' || $sampled === 'true');
        }

        if ($flags === '1') {
            $enabled = true;
        }

        return new SpanContext($traceId, $spanId, $enabled, true);
    }

    /**
     * Inject the SpanContext back into the response
     *
     * @param SpanContext $context
     * @param mixed $container
     * @return array
     */
    public function inject(SpanContext $context, $container)
    {
        if (headers_sent() === false) {
            header(self::X_B3_TRACE_ID. ': '.$context->traceId());
            header(self::X_B3_SAMPLED. ': '.$context->enabled() ? 1 : 0);
            header(self::X_B3_SPAN_ID. ': '.$context->spanId());
        }
        return [
                self::HTTP_X_B3_TRACE_ID => $context->traceId(),
                self::HTTP_X_B3_SPAN_ID => $context->spanId(),
                self::HTTP_X_B3_SAMPLED => $context->enabled() ? 1 : 0,
            ] + $container;
    }

    public function formatter()
    {
    }

    public function key()
    {
    }
}
