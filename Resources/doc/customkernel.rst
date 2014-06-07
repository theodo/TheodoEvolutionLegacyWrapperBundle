Create your own kernel
======================

First create a class that implements the ``LegacyKernelInterface`` or extends the ``LegacyKernel``:

::

    namespace Acme\MyLegacyBundle\Kernel;

    use Symfony\Component\HttpFoundation\Response;
    use Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel\LegacyKernel;

    class MyKernel extends LegacyKernel
    {
        // ... implementation of abstract methods

        /**
         * {@inheritdoc}
         */
        public function getName()
        {
            return 'mykernel';
        }
    }

Then you must implement the ``boot`` method. It must load the legacy application either by calling
an injected ``LegacyClassLoaderInterface`` implementation instance, or doing it directly from
the method. The way you should boot your legacy app depends on it, so this example is a really simple
one...

::

    /**
     * {@inheritdoc}
     */
    public function boot(ContainerInterface $container)
    {
        $this->classLoader->autoload();
    }

The next step is to implement the request handling. This is done implementing the ``handle`` method,
provided by the ``HttpKernelInterface``.
The first step is to save the eventually opened session to let the legacy application write data in it.
Then start a buffer and let the legacy handle the request as it normally does... Once this is done,
store the content and close the buffer. Then, if the legacy application does not, write close the
session and restart the Symfony 2 session. Finally, create a Symfony response populated with the content
that the legacy returned:

::

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        // Save the Symfony 2 session
        $session = $request->getSession();
        if ($session->isStarted()) {
            $session->save();
        }

        ob_start();
        require_once $this->getRootDir().'/index.php';
        $response = ob_get_clean();

        // Save the session updated by the legacy app and restart the Symfony 2 one.
        $session->migrate();

        return new Response($response);
    }

You might set different status code to the response, set http cache headers, change the content type... You can
do this from this point.