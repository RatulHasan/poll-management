export default function LiveResults() {
    return (
        <div className="flex items-center space-x-2 text-sm text-green-600 dark:text-green-400">
            <span className="relative flex h-2.5 w-2.5">
                <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75"></span>
                <span className="relative inline-flex h-2.5 w-2.5 rounded-full bg-green-500"></span>
            </span>
            <span>Live results</span>
        </div>
    );
}
