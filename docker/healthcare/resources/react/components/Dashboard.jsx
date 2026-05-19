import React from 'react';

import {
    LineChart,
    Line,
    XAxis,
    YAxis,
    Tooltip,
    ResponsiveContainer
} from 'recharts';

export default function Dashboard({ metrics }) {

    return (
        <div className="grid grid-cols-2 gap-6">

            <div className="bg-white rounded-2xl p-6 shadow-sm">

                <h2 className="text-lg font-semibold mb-4">
                    Admissions
                </h2>

                <ResponsiveContainer width="100%" height={300}>
                    <LineChart data={metrics}>
                        <XAxis dataKey="date" />
                        <YAxis />
                        <Tooltip />

                        <Line
                            type="monotone"
                            dataKey="count"
                        />
                    </LineChart>
                </ResponsiveContainer>
            </div>
        </div>
    );
}